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

    // ==================== Program ====================

    public function visitProgram(ProgramContext $ctx) {
        $this->code->comment("Configurando el frame pointer");
        $this->code->mov($this->r["FP"], $this->r["SP"]);
                  
        foreach ($ctx->stmt() as $stmt) {            
            $this->visit($stmt);
        }
        $this->code->endProgram();
        return $this->code;
    }

    // ==================== Statements ====================

    public function visitPrintStatement(PrintStatementContext $ctx) {        
        $type = $this->visit($ctx->e());
        $this->code->comment("Imprimiendo el resultado de la expresión");
        $this->code->comment("Cargando el valor a imprimir en A0");
        $this->code->pop($this->r["A0"]);        
        $this->code->printInt($this->r["A0"]);
    }

    public function visitVarDeclaration(VarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();

        // 1. Evaluate initializer → result pushed onto expression stack, get inferred type
        $type = $this->visit($ctx->e());

        // 2. Allocate slot
        $this->stackOffset -= 8;
        $offset = $this->stackOffset;

        // 3. Store metadata in current environment
        $this->env->set($varName, [
            "type" => $type,
            "offset" => $offset
        ]);

        // 4. Pop expression result, reserve space, store at variable slot
        $this->code->comment("Declaración de variable: " . $varName . " (" . $type . ") en [FP, #" . $offset . "]");
        $this->code->pop($this->r["T0"]);
        $this->code->subi($this->r["SP"], $this->r["SP"], 8);
        $this->code->str($this->r["T0"], $this->r["FP"], $offset);
    }

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();

        // 1. Evaluate expression
        $exprType = $this->visit($ctx->e());

        // 2. Resolve variable (walks parent chain)
        $symbol = $this->env->get($varName);
        $offset = $symbol["offset"];

        // 3. Type check: expression type must match declared type
        if ($exprType !== $symbol["type"]) {
            throw new Exception("No se puede asignar tipo " . $exprType . " a variable '" . $varName . "' de tipo " . $symbol["type"]);
        }

        // 4. Pop result, store at variable's FP-relative slot
        $this->code->comment("Asignación a variable: " . $varName . " en [FP, #" . $offset . "]");
        $this->code->pop($this->r["T0"]);
        $this->code->str($this->r["T0"], $this->r["FP"], $offset);
    }

    public function visitIfStatement(IfStatementContext $ctx) {
        // 1. Evaluate condition
        $condType = $this->visit($ctx->e());

        if ($condType !== "int" && $condType !== "bool") {
            throw new Exception("La condición del 'if' debe ser de tipo int o bool, se obtuvo " . $condType);
        }

        // 2. Pop condition into T0
        $this->code->comment("Evaluando condición del if");
        $this->code->pop($this->r["T0"]);

        $hasElse = ($ctx->else() !== null);

        if ($hasElse) {
            $elseLabel = $this->newLabel();
            $endLabel = $this->newLabel();

            // 3. If false, jump to else
            $this->code->cbz($this->r["T0"], $elseLabel);

            // 4. Visit if-body
            $this->code->comment("Cuerpo del if");
            $this->visit($ctx->block());

            // 5. Skip else
            $this->code->b($endLabel);

            // 6. Else body
            $this->code->label($elseLabel);
            $this->code->comment("Cuerpo del else");
            $this->visit($ctx->else());

            // 7. End
            $this->code->label($endLabel);
        } else {
            $endLabel = $this->newLabel();

            // 3. If false, jump to end
            $this->code->cbz($this->r["T0"], $endLabel);

            // 4. Visit if-body
            $this->code->comment("Cuerpo del if");
            $this->visit($ctx->block());

            // 5. End
            $this->code->label($endLabel);
        }
    }

    public function visitWhileStatement(WhileStatementContext $ctx) {
        $startLabel = $this->newLabel();
        $endLabel = $this->newLabel();

        // Push loop labels for break/continue
        $this->loopLabels[] = ["start" => $startLabel, "end" => $endLabel];

        // 1. Loop start label
        $this->code->label($startLabel);

        // 2. Evaluate condition
        $condType = $this->visit($ctx->e());

        if ($condType !== "int" && $condType !== "bool") {
            throw new Exception("La condición del 'while' debe ser de tipo int o bool, se obtuvo " . $condType);
        }

        // 3. Pop condition, branch if false
        $this->code->comment("Evaluando condición del while");
        $this->code->pop($this->r["T0"]);
        $this->code->cbz($this->r["T0"], $endLabel);

        // 4. Visit loop body
        $this->code->comment("Cuerpo del while");
        $this->visit($ctx->block());

        // 5. Jump back to condition
        $this->code->b($startLabel);

        // 6. End label
        $this->code->label($endLabel);

        // Pop loop labels
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

    // ==================== Block ====================

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $prevOffset = $this->stackOffset;
        $this->env = new Environment($prevEnv);

        $this->code->comment("Entrando a nuevo bloque/scope");

        foreach ($ctx->stmt() as $stmt) {            
            $flow = $this->visit($stmt);            
            if ($flow instanceof FlowType) {
                // Reclaim stack before propagating flow
                if ($this->stackOffset !== $prevOffset) {
                    $bytesToReclaim = $prevOffset - $this->stackOffset;
                    $this->code->addi($this->r["SP"], $this->r["SP"], $bytesToReclaim);
                }
                $this->stackOffset = $prevOffset;
                $this->env = $prevEnv;                
                return $flow;
            }
        }

        // Normal exit: reclaim local variables
        if ($this->stackOffset !== $prevOffset) {
            $bytesToReclaim = $prevOffset - $this->stackOffset;
            $this->code->comment("Saliendo del bloque, recuperando " . $bytesToReclaim . " bytes");
            $this->code->addi($this->r["SP"], $this->r["SP"], $bytesToReclaim);
        }
        $this->stackOffset = $prevOffset;
        $this->env = $prevEnv;
    }

    // ==================== Expressions ====================

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        $leftType = $this->visit($ctx->left);

        if ($ctx->right !== null) {
            $rightType = $this->visit($ctx->right);

            if ($leftType !== $rightType) {
                throw new Exception("Operador '==' requiere operandos del mismo tipo, se obtuvo " . $leftType . " y " . $rightType);
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

            if ($leftType !== "int" || $rightType !== "int") {
                throw new Exception("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . $leftType . " y " . $rightType);
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

            if ($leftType !== "int" || $rightType !== "int") {
                throw new Exception("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . $leftType . " y " . $rightType);
            }

            $this->code->comment("Visitando expresión de suma/resta: " . $op);
            $this->code->comment("Evaluando el primer operando");
            $this->code->pop($this->r["T0"]);
            $this->code->comment("Evaluando el segundo operando");
            $this->code->pop($this->r["T1"]);

            switch ($op) {
                case '+':
                    $this->code->comment("Sumando T0 con T1");
                    $this->code->add($this->r["T0"], $this->r["T0"], $this->r["T1"]);
                    $this->code->push($this->r["T0"]);
                    break;
                case '-':
                    $this->code->comment("Restando T0 con T1");
                    $this->code->sub($this->r["T0"], $this->r["T0"], $this->r["T1"]);
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

            if ($leftType !== "int" || $rightType !== "int") {
                throw new Exception("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . $leftType . " y " . $rightType);
            }

            $this->code->comment("Visitando expresión de producto: " . $op);
            $this->code->comment("Evaluando el primer operando");
            $this->code->pop($this->r["T0"]);
            $this->code->comment("Evaluando el segundo operando");
            $this->code->pop($this->r["T1"]);

            switch ($op) {
                case '*':
                    $this->code->comment("Multiplicando T0 con T1");  
                    $this->code->mul($this->r["T0"], $this->r["T0"], $this->r["T1"]);
                    $this->code->push($this->r["T0"]);                  
                    break;                 
                case '/':
                    $this->code->comment("Dividiendo T0 con T1");
                    $this->code->div($this->r["T0"], $this->r["T0"], $this->r["T1"]);
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

        if ($type !== "int") {
            throw new Exception("Operador '-' (unario) requiere operando de tipo int, se obtuvo " . $type);
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

    public function visitBoolExpression(BoolExpressionContext $ctx) {
        $value = $ctx->bool->getText();
        $intVal = ($value === "true") ? 1 : 0;
        $this->code->comment("Cargando booleano: " . $value);
        $this->code->li($this->r["T0"], $intVal);
        $this->code->push($this->r["T0"]);
        return "bool";
    }
}
