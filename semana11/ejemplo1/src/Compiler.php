<?php 

use Context\ProgramContext;
use Context\PrintStatementContext;
use Context\VarDeclarationContext;
use Context\AssignmentStatementContext;
use Context\IfStatementContext;
use Context\WhileStatementContext;
use Context\ContinueStatementContext;
use Context\BreakStatementContext;
use Context\ReturnStatementContext;
use Context\FunctionDeclarationContext;
use Context\FunctionCallStatementContext;
use Context\ArrayAssignmentStatementContext;
use Context\BlockStatementContext;
use Context\EqualityExpressionContext;
use Context\InequalityExpressionContext;
use Context\AddExpressionContext;
use Context\ProductExpressionContext;
use Context\PrimaryExpressionContext;
use Context\UnaryExpressionContext;
use Context\GroupedExpressionContext;
use Context\IntExpressionContext;
use Context\ReferenceExpressionContext;
use Context\BoolExpressionContext;
use Context\FunctionCallExpressionContext;
use Context\ArrayExpressionContext;
use Context\ArrayAccessExpressionContext;
use Context\ParameterListContext;
use Context\ArgumentListContext;



class Compiler extends GrammarBaseVisitor {
    public $code;     
    public $r;
    public $env;
    public $stackOffset;
    public $labelCounter;
    public $loopLabels;

    public function __construct() {
        $this->code = new ASMGenerator();                
        $this->r = include __DIR__ . "/ARM/Constants.php";
        $this->env = new Environment();
        $this->stackOffset = 0;
        $this->labelCounter = 0;
        $this->loopLabels = [];
    }

    public function newLabel() {
        return "L" . $this->labelCounter++;
    }

    private function emitHeapAllocFixed($bytes, $resultReg) {
        $this->code->comment("Heap alloc de " . $bytes . " bytes");
        $this->code->mov($resultReg, $this->r["HP"]);
        $this->code->li($this->r["T0"], $bytes);
        $this->code->add($this->r["T1"], $this->r["HP"], $this->r["T0"]);
        $this->code->cmp($this->r["T1"], $this->r["HEAP_END"]);
        $this->code->bcond("hi", "_panic_oom");
        $this->code->mov($this->r["HP"], $this->r["T1"]);
    }    

    private function emitBoundsCheckAtDim($baseReg, $idxReg, $dimIndex) {
        $dimOffset = 8 + ($dimIndex * 8);
        $this->code->cmp($idxReg, "#0");
        $this->code->bcond("lt", "_panic_oob");
        $this->code->ldr($this->r["T2"], $baseReg, $dimOffset);
        $this->code->cmp($idxReg, $this->r["T2"]);
        $this->code->bcond("ge", "_panic_oob");
    }

    private function emitRowMajorAddrFromIndexes($baseReg, $indexes, $rank, $addrReg) {
        if (count($indexes) !== $rank) {
            throw new Exception("Se esperaban " . $rank . " índices para acceso row-major, se recibieron " . count($indexes));
        }

        $this->code->comment("Row Major!!");
        foreach ($indexes as $dim => $indexExpr) {
            $indexType = $this->visit($indexExpr);
            if (!CompilerSupport::isIntType($indexType)) {
                throw new Exception("El índice de array debe ser int, se obtuvo " . CompilerSupport::typeToString($indexType));
            }

            $this->code->pop($this->r["T0"]);
            $this->emitBoundsCheckAtDim($baseReg, $this->r["T0"], $dim);

            if ($dim === 0) {
                $this->code->mov($this->r["T3"], $this->r["T0"]);
                continue;
            }

            $dimOffset = 8 + ($dim * 8);
            $this->code->ldr($this->r["T2"], $baseReg, $dimOffset);
            $this->code->mul($this->r["T3"], $this->r["T3"], $this->r["T2"]);
            $this->code->add($this->r["T3"], $this->r["T3"], $this->r["T0"]);
        }

        $headerBytes = ($rank + 1) * 8;
        $this->code->li($this->r["T2"], 8);
        $this->code->mul($this->r["T3"], $this->r["T3"], $this->r["T2"]);
        $this->code->add($addrReg, $baseReg, $this->r["T3"]);
        $this->code->addi($addrReg, $addrReg, $headerBytes);
    }

    private function emitRuntimeErrorHandlers() {
        $this->code->label("_panic_oob");
        $this->code->li($this->r["A0"], 2);
        $this->code->li($this->r["SYS"], 93);
        $this->code->syscall();

        $this->code->label("_panic_oom");
        $this->code->li($this->r["A0"], 1);
        $this->code->li($this->r["SYS"], 93);
        $this->code->syscall();
    }

    public function visitProgram(ProgramContext $ctx) {
        $this->code->comment("Configurando el frame pointer");
        $this->code->mov($this->r["FP"], $this->r["SP"]);
        $this->code->comment("Inicializando heap pointer y límite de heap");
        $this->code->ldrl($this->r["HP"], "heap_base");
        $this->code->ldrl($this->r["HEAP_END"], "heap_end");
                   
        foreach ($ctx->stmt() as $stmt) {            
            $this->visit($stmt);
        }
        $this->code->endProgram();
        $this->emitRuntimeErrorHandlers();
        return $this->code;
    }    

    public function visitPrintStatement(PrintStatementContext $ctx) {        
        $type = $this->visit($ctx->e());
        if (!CompilerSupport::isIntOrBoolType($type)) {
            throw new Exception("print solo admite int o bool, se obtuvo " . CompilerSupport::typeToString($type));
        }
        $this->code->comment("Imprimiendo el resultado de la expresión");
        $this->code->comment("Cargando el valor a imprimir en A0");
        $this->code->pop($this->r["A0"]);        
        $this->code->printInt($this->r["A0"]);
    }

    public function visitVarDeclaration(VarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();

        // 1. Evaluar inicialización para obtener el tipo de la variable
        $type = $this->visit($ctx->e());

        // 2. Asignar espacio en el stack para la nueva variable 
        $this->stackOffset -= 8;
        $offset = $this->stackOffset;

        // 3. Guardar meta-data en el entorno (type, offset)
        $this->env->set($varName, [
            "type" => $type,
            "offset" => $offset
        ]);

        // 4. Pop de la expresión, reservar espacio en el stack, almacenar el valor de la expresión 
        $this->code->comment("Declaración de variable: " . $varName . " (" . CompilerSupport::typeToString($type) . ") en [FP, #" . $offset . "]");
        $this->code->pop($this->r["T0"]);
        $this->code->subi($this->r["SP"], $this->r["SP"], 8);
        $this->code->str($this->r["T0"], $this->r["FP"], $offset);
    }

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();

        // 1. Evaluar expresión para obtener su tipo
        $exprType = $this->visit($ctx->e());

        // 2. Resolver variable en el entorno para obtener su tipo y offset
        $symbol = $this->env->get($varName);
        $offset = $symbol["offset"];

        // 3. Chequeo de tipos: el tipo de la expresión debe ser compatible con el tipo de la variable
        if (!CompilerSupport::typeEquals($exprType, $symbol["type"])) {
            throw new Exception("No se puede asignar tipo " . CompilerSupport::typeToString($exprType) . " a variable '" . $varName . "' de tipo " . CompilerSupport::typeToString($symbol["type"]));
        }

        // 4. Pop del resultado de la expresión, almacenar el valor en el stack en la posición correspondiente a la variable
        $this->code->comment("Asignación a variable: " . $varName . " en [FP, #" . $offset . "]");
        $this->code->pop($this->r["T0"]);
        $this->code->str($this->r["T0"], $this->r["FP"], $offset);
    }

    public function visitIfStatement(IfStatementContext $ctx) {
        // 1. Evaluar condición y verificar que sea de tipo int o bool
        $condType = $this->visit($ctx->e());

        if (!CompilerSupport::isIntOrBoolType($condType)) {
            throw new Exception("La condición del 'if' debe ser de tipo int o bool, se obtuvo " . CompilerSupport::typeToString($condType));
        }

        // 2. Pop de la condición a un registro temporal (e.g. T0)
        $this->code->comment("Evaluando condición del if");
        $this->code->pop($this->r["T0"]);

        $hasElse = ($ctx->else() !== null);

        if ($hasElse) {
            $elseLabel = $this->newLabel();
            $endLabel = $this->newLabel();

            // 3. Si es falso salta a else
            $this->code->cbz($this->r["T0"], $elseLabel);

            // 4. Visitar el cuerpo del If
            $this->code->comment("Cuerpo del if");
            $this->visit($ctx->block());

            // 5. Saltar el else
            $this->code->b($endLabel);

            // 6. Cuerpo del else
            $this->code->label($elseLabel);
            $this->code->comment("Cuerpo del else");
            $this->visit($ctx->else());

            // 7. Final
            $this->code->label($endLabel);
        } else {
            $endLabel = $this->newLabel();

            // 3. Si es falso salto al final
            $this->code->cbz($this->r["T0"], $endLabel);

            // 4. Visitar el cuerpo del If
            $this->code->comment("Cuerpo del if");
            $this->visit($ctx->block());

            // 5. Final
            $this->code->label($endLabel);
        }
    }

    public function visitWhileStatement(WhileStatementContext $ctx) {
        $startLabel = $this->newLabel();
        $endLabel = $this->newLabel();

        // Pushear los labels del ciclo actual para soportar break/continue
        $this->loopLabels[] = ["start" => $startLabel, "end" => $endLabel];

        // 1. Etiquetar el inicio del ciclo
        $this->code->label($startLabel);

        // 2. Evaluar condición
        $condType = $this->visit($ctx->e());

        if (!CompilerSupport::isIntOrBoolType($condType)) {
            throw new Exception("La condición del 'while' debe ser de tipo int o bool, se obtuvo " . CompilerSupport::typeToString($condType));
        }

        // 3. Pop de la condición a un registro temporal (e.g. T0), si es falso saltar al final del ciclo
        $this->code->comment("Evaluando condición del while");
        $this->code->pop($this->r["T0"]);
        $this->code->cbz($this->r["T0"], $endLabel);

        // 4. Visitar el cuerpo del while
        $this->code->comment("Cuerpo del while");
        $this->visit($ctx->block());

        // 5. Saltar a la condición
        $this->code->b($startLabel);

        // 6. Etiqueta final
        $this->code->label($endLabel);

        // Pop de los labels del ciclo actual
        array_pop($this->loopLabels);
    }

    public function visitContinueStatement(ContinueStatementContext $ctx) {
        if (empty($this->loopLabels)) {
            throw new Exception("'continue' fuera de un ciclo while");
        }

        $loop = end($this->loopLabels);
        $this->code->comment("Continue: saltar al inicio del while");
        $this->code->b($loop["start"]);
        return new ContinueType();
    }

    public function visitBreakStatement(BreakStatementContext $ctx) {
        if (empty($this->loopLabels)) {
            throw new Exception("'break' fuera de un ciclo while");
        }

        $loop = end($this->loopLabels);
        $this->code->comment("Break: saltar al final del while");
        $this->code->b($loop["end"]);
        return new BreakType();
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $prevOffset = $this->stackOffset;
        $this->env = new Environment($prevEnv);

        $this->code->comment("Entrando a nuevo bloque/scope");

        foreach ($ctx->stmt() as $stmt) {            
            $flow = $this->visit($stmt);            
            if ($flow instanceof FlowType) {
                // Reclamar el stack de variables locales antes de salir del bloque
                if ($this->stackOffset !== $prevOffset) {
                    $bytesToReclaim = $prevOffset - $this->stackOffset;
                    $this->code->addi($this->r["SP"], $this->r["SP"], $bytesToReclaim);
                }
                $this->stackOffset = $prevOffset;
                $this->env = $prevEnv;                
                return $flow;
            }
        }

        // Normal: Reclamar el stack de variables locales al salir del bloque
        if ($this->stackOffset !== $prevOffset) {
            $bytesToReclaim = $prevOffset - $this->stackOffset;
            $this->code->comment("Saliendo del bloque, recuperando " . $bytesToReclaim . " bytes");
            $this->code->addi($this->r["SP"], $this->r["SP"], $bytesToReclaim);
        }
        $this->stackOffset = $prevOffset;
        $this->env = $prevEnv;
    }    

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        $leftType = $this->visit($ctx->left);

        if ($ctx->right !== null) {
            $rightType = $this->visit($ctx->right);

            if (!CompilerSupport::typeEquals($leftType, $rightType)) {
                throw new Exception("Operador '==' requiere operandos del mismo tipo, se obtuvo " . CompilerSupport::typeToString($leftType) . " y " . CompilerSupport::typeToString($rightType));
            }

            $this->code->comment("Visitando expresión de igualdad: ==");
            $this->code->pop($this->r["T0"]);
            $this->code->pop($this->r["T1"]);
            $this->code->comment("Comparando T1 == T0");
            $this->code->cmp($this->r["T1"], $this->r["T0"]);
            $this->code->cset($this->r["T0"], "eq");
            $this->code->push($this->r["T0"]);
            return "bool";
        }

        return $leftType;
    }

    public function visitInequalityExpression(InequalityExpressionContext $ctx) {
        $leftType = $this->visit($ctx->left);

        if ($ctx->right !== null) {
            $rightType = $this->visit($ctx->right);
            $op = $ctx->op->getText();

            if (!CompilerSupport::isIntType($leftType) || !CompilerSupport::isIntType($rightType)) {
                throw new Exception("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . CompilerSupport::typeToString($leftType) . " y " . CompilerSupport::typeToString($rightType));
            }

            $this->code->comment("Visitando expresión de desigualdad: " . $op);
            $this->code->pop($this->r["T0"]);
            $this->code->pop($this->r["T1"]);
            $this->code->comment("Comparando T1 " . $op . " T0");
            $this->code->cmp($this->r["T1"], $this->r["T0"]);
            $cond = ($op === ">") ? "gt" : "lt";
            $this->code->cset($this->r["T0"], $cond);
            $this->code->push($this->r["T0"]);
            return "bool";
        }

        return $leftType;
    }

    public function visitAddExpression(AddExpressionContext $ctx) {            
        if ($ctx->add() !== null) {
            $leftType = $this->visit($ctx->add());
            $rightType = $this->visit($ctx->prod());
            $op = $ctx->op->getText();

            if (!CompilerSupport::isIntType($leftType) || !CompilerSupport::isIntType($rightType)) {
                throw new Exception("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . CompilerSupport::typeToString($leftType) . " y " . CompilerSupport::typeToString($rightType));
            }

            $this->code->comment("Visitando expresión de suma/resta: " . $op);
            $this->code->comment("Evaluando el segundo operando");
            $this->code->pop($this->r["T0"]);
            $this->code->comment("Evaluando el primer operando");
            $this->code->pop($this->r["T1"]);

            switch ($op) {
                case '+':
                    $this->code->comment("Sumando T1 con T0");
                    $this->code->add($this->r["T0"], $this->r["T1"], $this->r["T0"]);
                    $this->code->push($this->r["T0"]);
                    break;
                case '-':
                    $this->code->comment("Restando T1 con T0");
                    $this->code->sub($this->r["T0"], $this->r["T1"], $this->r["T0"]);
                    $this->code->push($this->r["T0"]);
                    break;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
            return "int";
        } else {
            return $this->visit($ctx->prod());
        }
    }

    public function visitProductExpression(ProductExpressionContext $ctx) {                
        if ($ctx->prod() !== null) {
            $leftType = $this->visit($ctx->prod());
            $rightType = $this->visit($ctx->unary());
            $op = $ctx->op->getText();

            if (!CompilerSupport::isIntType($leftType) || !CompilerSupport::isIntType($rightType)) {
                throw new Exception("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . CompilerSupport::typeToString($leftType) . " y " . CompilerSupport::typeToString($rightType));
            }

            $this->code->comment("Visitando expresión de producto: " . $op);
            $this->code->comment("Evaluando el segundo operando");
            $this->code->pop($this->r["T0"]);
            $this->code->comment("Evaluando el primer operando");
            $this->code->pop($this->r["T1"]);

            switch ($op) {
                case '*':
                    $this->code->comment("Multiplicando T1 con T0");  
                    $this->code->mul($this->r["T0"], $this->r["T1"], $this->r["T0"]);
                    $this->code->push($this->r["T0"]);                  
                    break;                 
                case '/':
                    $this->code->comment("Dividiendo T1 con T0");
                    $this->code->div($this->r["T0"], $this->r["T1"], $this->r["T0"]);
                    $this->code->push($this->r["T0"]);
                    break;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
            return "int";
        } else {
            return $this->visit($ctx->unary());
        }   
    }

    public function visitPrimaryExpression(PrimaryExpressionContext $ctx) {
        return $this->visit($ctx->primary());
    }

    public function visitUnaryExpression(UnaryExpressionContext $ctx) {       
        $this->code->comment("Visitando expresión unaria"); 
        $type = $this->visit($ctx->unary());

        if (!CompilerSupport::isIntType($type)) {
            throw new Exception("Operador '-' (unario) requiere operando de tipo int, se obtuvo " . CompilerSupport::typeToString($type));
        }

        $this->code->comment("Cargando el valor en T0");    
        $this->code->pop($this->r["T0"]);
        $this->code->comment("Negando el valor en T0");
        $this->code->sub($this->r["T0"], $this->r["ZERO"], $this->r["T0"]);
        $this->code->push($this->r["T0"]);
        return "int";
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->e());
    }

    public function visitIntExpression(IntExpressionContext $ctx) {
        $this->code->comment("Cargando entero: " . $ctx->INT()->getText());
        $number = intval($ctx->INT()->getText());
        $this->code->li($this->r["T0"], $number);
        $this->code->push($this->r["T0"]);
        return "int";
    }

    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        $symbol = $this->env->get($varName);
        $offset = $symbol["offset"];

        $this->code->comment("Referencia a variable: " . $varName . " en [FP, #" . $offset . "]");
        $this->code->ldr($this->r["T0"], $this->r["FP"], $offset);
        $this->code->push($this->r["T0"]);
        return $symbol["type"];
    }

    public function visitArrayExpression(ArrayExpressionContext $ctx) {
        $analysis = CompilerSupport::analyzeArrayLiteralNode($ctx, function($node) {
            return $this->visit($node);
        });
        $elemType = $analysis["scalarType"];
        $dims = $analysis["dims"];
        $rank = count($dims);

        $totalElements = 1;
        foreach ($dims as $d) {
            $totalElements *= $d;
        }

        $headerBytes = ($rank + 1) * 8;
        $bytes = $headerBytes + ($totalElements * 8);

        $this->emitHeapAllocFixed($bytes, $this->r["T4"]);

        $this->code->comment("Alojando en el Heap");
        $this->code->li($this->r["T0"], $rank);
        $this->code->str($this->r["T0"], $this->r["T4"], 0);
        for ($i = 0; $i < $rank; $i++) {
            $this->code->li($this->r["T0"], $dims[$i]);
            $this->code->str($this->r["T0"], $this->r["T4"], 8 + ($i * 8));
        }

        for ($i = $totalElements - 1; $i >= 0; $i--) {
            $offset = $headerBytes + ($i * 8);
            $this->code->pop($this->r["T0"]);
            $this->code->str($this->r["T0"], $this->r["T4"], $offset);
        }

        $this->code->push($this->r["T4"]);
        return CompilerSupport::makeArrayTypeWithRankAndDims($elemType, $rank, $dims);
    }

    public function visitArrayAccessExpression(ArrayAccessExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        $symbol = $this->env->get($varName);
        $indexes = CompilerSupport::getArrayAccessIndexes($ctx);

        if (count($indexes) === 0) {
            throw new Exception("Acceso a array sin índices");
        }

        $currentType = $symbol["type"];
        if (!CompilerSupport::isArrayType($currentType)) {
            throw new Exception("Se intentó indexar un valor no-array en '" . $varName . "'");
        }

        $rank = array_key_exists("rank", $currentType) ? $currentType["rank"] : 1;
        if (count($indexes) !== $rank) {
            throw new Exception("Se esperaban " . $rank . " índices para '" . $varName . "', se recibieron " . count($indexes));
        }

        $this->code->ldr($this->r["T1"], $this->r["FP"], $symbol["offset"]);
        $this->emitRowMajorAddrFromIndexes($this->r["T1"], $indexes, $rank, $this->r["T2"]);
        $this->code->ldr($this->r["T3"], $this->r["T2"], 0);
        $this->code->push($this->r["T3"]);
        return $currentType["elem"];
    }

    public function visitArrayAssignmentStatement(ArrayAssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $symbol = $this->env->get($varName);
        $parts = CompilerSupport::getArrayAssignParts($ctx);
        $indexes = $parts["indexes"];
        $assignExpr = $parts["assign"];

        if ($assignExpr === null || count($indexes) === 0) {
            throw new Exception("Asignación a array inválida");
        }

        $assignType = $this->visit($assignExpr);
        $currentType = $symbol["type"];
        if (!CompilerSupport::isArrayType($currentType)) {
            throw new Exception("Se intentó indexar un valor no-array en '" . $varName . "'");
        }

        $rank = array_key_exists("rank", $currentType) ? $currentType["rank"] : 1;
        if (count($indexes) !== $rank) {
            throw new Exception("Se esperaban " . $rank . " índices para asignar en '" . $varName . "', se recibieron " . count($indexes));
        }

        $this->code->ldr($this->r["T1"], $this->r["FP"], $symbol["offset"]);

        $expectedType = $currentType["elem"];
        if (!CompilerSupport::typeEquals($assignType, $expectedType)) {
            throw new Exception("Tipo incompatible en asignación de array, se esperaba " . CompilerSupport::typeToString($expectedType) . " y se obtuvo " . CompilerSupport::typeToString($assignType));
        }

        $this->emitRowMajorAddrFromIndexes($this->r["T1"], $indexes, $rank, $this->r["T2"]);
        $this->code->pop($this->r["T3"]);
        $this->code->str($this->r["T3"], $this->r["T2"], 0);
        return null;
    }

    public function visitBoolExpression(BoolExpressionContext $ctx) {
        $value = $ctx->bool->getText();
        $intVal = ($value === "true") ? 1 : 0;
        $this->code->comment("Cargando booleano: " . $value);
        $this->code->li($this->r["T0"], $intVal);
        $this->code->push($this->r["T0"]);
        return "bool";
    }
}
