<?php

use Context\AddExpressionContext;
use Context\AssignmentStatementContext;
use Context\ArgumentListContext;
use Context\ArrayAccessExpressionContext;
use Context\ArrayAssignmentStatementContext;
use Context\ArrayExpressionContext;
use Context\ArrayReferenceExpressionContext;
use Context\BlockStatementContext;
use Context\BoolExpressionContext;
use Context\BreakStatementContext;
use Context\ContinueStatementContext;
use Context\ElseContext;
use Context\EContext;
use Context\EqualityExpressionContext;
use Context\ExpressionListContext;
use Context\FunctionCallExpressionContext;
use Context\FunctionCallStatementContext;
use Context\FunctionDeclarationContext;
use Context\GroupedExpressionContext;
use Context\IfStatementContext;
use Context\InequalityExpressionContext;
use Context\InitArrayReferenceExpressionContext;
use Context\InitExpressionListContext;
use Context\IntExpressionContext;
use Context\ParameterListContext;
use Context\PrimaryExpressionContext;
use Context\PrintStatementContext;
use Context\ProductExpressionContext;
use Context\ProgramContext;
use Context\ReferenceExpressionContext;
use Context\ReturnStatementContext;
use Context\TransformRowMajorContext;
use Context\UnaryExpressionContext;
use Context\VarDeclarationContext;
use Context\WhileStatementContext;

class Compiler extends GrammarBaseVisitor {
    public $code;
    public $r;
    public $env;
    public $stackOffset;
    public $labelCounter;
    public $loopLabels;
    public $collectArrayLiteral;
    public $natives;
    public $usedNativeLabels;
    public $foreignFunctions;
    public $inFunction;
    public $currentFunctionReturnLabel;
    public $currentBaseReg;
    public $paramAnalyzer;
    public $symbolAccess;

    public function __construct() {
        $this->code = new ASMGenerator();
        $this->r = include __DIR__ . "/ARM/Constants.php";
        $this->env = new Environment();
        $this->stackOffset = 0;
        $this->labelCounter = 0;
        $this->loopLabels = [];
        $this->collectArrayLiteral = false;
        $this->natives = include __DIR__ . "/Natives.php";
        $this->usedNativeLabels = [];
        $this->foreignFunctions = [];
        $this->inFunction = false;
        $this->currentFunctionReturnLabel = null;
        $this->currentBaseReg = $this->r["FP"];
        $this->paramAnalyzer = new FunctionParamAnalyzer();
        $this->symbolAccess = new SymbolAccessEmitter($this->code, $this->r);

        foreach ($this->natives as $name => $descriptor) {
            $this->env->set($name, $descriptor);
        }
    }

    public function newLabel() {
        return "L" . $this->labelCounter++;
    }

    private function fail($message) {
        throw CompilerError::semantic($message);
    }

    private function symbolBaseReg($symbol) {
        if (is_array($symbol) && array_key_exists("baseReg", $symbol)) {
            return $symbol["baseReg"];
        }
        return $this->r["FP"];
    }

    private function compileCall($fnName, $argsCtx, $pushReturn) {
        try {
            $symbol = $this->env->get($fnName);
        } catch (Exception $e) {
            $this->fail("Funcion '" . $fnName . "' no definida");
        }

        if (!is_array($symbol)
            || !array_key_exists("kind", $symbol)
            || ($symbol["kind"] !== "native_fn" && $symbol["kind"] !== "foreign_fn")) {
            $this->fail("'" . $fnName . "' no es una funcion invocable");
        }

        $arity = $symbol["arity"];
        if ($arity > 8) {
            $this->fail("Funcion '" . $fnName . "' supera el maximo soportado de 8 argumentos");
        }

        $argExprs = [];
        if ($argsCtx !== null) {
            $argExprs = $this->visit($argsCtx);
        }

        if (!is_array($argExprs)) {
            $this->fail("Lista de argumentos invalida para '" . $fnName . "'");
        }

        if (count($argExprs) !== $arity) {
            $this->fail("'" . $fnName . "' espera " . $arity . " argumentos y se recibieron " . count($argExprs));
        }

        $argTypes = array_key_exists("argTypes", $symbol) ? $symbol["argTypes"] : [];
        $argModes = array_key_exists("argModes", $symbol) ? $symbol["argModes"] : [];
        $byRefSymbols = [];
        for ($i = 0; $i < count($argExprs); $i++) {
            $expected = $argTypes[$i];
            $mode = array_key_exists($i, $argModes) ? $argModes[$i] : "by_value";

            if ($mode === "by_ref") {
                $expr = $argExprs[$i];
                $exprText = $expr->getText();
                if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $exprText)) {
                    $this->fail("Argumento " . ($i + 1) . " de '" . $fnName . "' debe ser una variable para paso por referencia");
                }

                $argName = $exprText;
                $argSymbol = $this->env->get($argName);
                if (!is_array($argSymbol) || !array_key_exists("offset", $argSymbol)) {
                    $this->fail("Argumento " . ($i + 1) . " de '" . $fnName . "' no es una variable valida para referencia");
                }

                if (!TypeChecker::typeEquals($argSymbol["type"], $expected)) {
                    $this->fail("Argumento " . ($i + 1) . " de '" . $fnName . "' espera " . TypeChecker::typeToString($expected) . " y recibio " . TypeChecker::typeToString($argSymbol["type"]));
                }

                $byRefSymbols[$i] = $argSymbol;
                continue;
            }

            $type = $this->visit($argExprs[$i]);
            if (!TypeChecker::typeEquals($type, $expected)) {
                $this->fail("Argumento " . ($i + 1) . " de '" . $fnName . "' espera " . TypeChecker::typeToString($expected) . " y recibio " . TypeChecker::typeToString($type));
            }
        }

        $argRegs = [
            $this->r["A0"],
            $this->r["A1"],
            $this->r["A2"],
            $this->r["A3"],
            $this->r["A4"],
            $this->r["A5"],
            $this->r["A6"],
            $this->r["A7"],
        ];

        $this->code->comment("Preparando argumentos de llamada para '" . $fnName . "'");
        for ($i = count($argExprs) - 1; $i >= 0; $i--) {
            $mode = array_key_exists($i, $argModes) ? $argModes[$i] : "by_value";
            if ($mode === "by_ref") {
                $argSymbol = $byRefSymbols[$i];
                $baseReg = $this->symbolBaseReg($argSymbol);
                $this->code->comment("arg" . ($i + 1) . " by_ref: pasando direccion de variable");
                $this->symbolAccess->emitAddressOfSymbolToReg($argSymbol, $baseReg, $argRegs[$i]);
                continue;
            }
            $this->code->comment("arg" . ($i + 1) . " by_value: cargando valor en registro de argumento");
            $this->code->pop($argRegs[$i]);
        }

        $targetLabel = $symbol["label"];
        if ($symbol["kind"] === "native_fn") {
            $this->usedNativeLabels[$targetLabel] = true;
        }

        $alignedLabel = $this->newLabel();
        $doneLabel = $this->newLabel();
        $this->code->comment("Alineando SP a 16 bytes antes de bl");
        $this->code->andi($this->r["T5"], $this->r["SP"], 15);
        $this->code->cbz($this->r["T5"], $alignedLabel);
        $this->code->subi($this->r["SP"], $this->r["SP"], 8);
        $this->code->comment("Llamada a funcion con padding temporal de stack");
        $this->code->bl($targetLabel);
        $this->code->addi($this->r["SP"], $this->r["SP"], 8);
        $this->code->b($doneLabel);
        $this->code->label($alignedLabel);
        $this->code->comment("Llamada a funcion sin padding extra");
        $this->code->bl($targetLabel);
        $this->code->label($doneLabel);

        if ($pushReturn) {
            $this->code->push($this->r["A0"]);
            return $symbol["returnType"];
        }

        return null;
    }

    private function emitForeignFunction($descriptor) {
        $ctx = $descriptor["ctx"];
        $label = $descriptor["label"];
        $params = $descriptor["params"];
        $closureEnv = $descriptor["closureEnv"];

        $savedEnv = $this->env;
        $savedStackOffset = $this->stackOffset;
        $savedLoopLabels = $this->loopLabels;
        $savedInFunction = $this->inFunction;
        $savedReturnLabel = $this->currentFunctionReturnLabel;
        $savedBaseReg = $this->currentBaseReg;

        $this->env = new Environment($closureEnv);
        $this->stackOffset = 0;
        $this->loopLabels = [];
        $this->inFunction = true;
        $this->currentFunctionReturnLabel = $this->newLabel();
        $this->currentBaseReg = $this->r["FP"];

        $this->code->label($label);
        $this->code->comment("Prologo de funcion foreign '" . $descriptor["name"] . "'");
        $this->code->stpPre($this->r["FP"], $this->r["RA"], $this->r["SP"], -16);
        $this->code->mov($this->r["FP"], $this->r["SP"]);

        $argRegs = [
            $this->r["A0"],
            $this->r["A1"],
            $this->r["A2"],
            $this->r["A3"],
            $this->r["A4"],
            $this->r["A5"],
            $this->r["A6"],
            $this->r["A7"],
        ];

        $argTypes = array_key_exists("argTypes", $descriptor) ? $descriptor["argTypes"] : [];
        $argModes = array_key_exists("argModes", $descriptor) ? $descriptor["argModes"] : [];

        for ($i = 0; $i < count($params); $i++) {
            $this->stackOffset -= 8;
            $offset = $this->stackOffset;

            $paramType = array_key_exists($i, $argTypes) ? $argTypes[$i] : "int";
            $paramMode = array_key_exists($i, $argModes) ? $argModes[$i] : "by_value";
            $this->env->set($params[$i], [
                "type" => $paramType,
                "offset" => $offset,
                "baseReg" => $this->currentBaseReg,
                "byRef" => ($paramMode === "by_ref")
            ]);
            $this->code->comment("Materializando parametro '" . $params[$i] . "' (" . $paramMode . ") en frame local");
            $this->code->subi($this->r["SP"], $this->r["SP"], 8);
            $this->code->str($argRegs[$i], $this->r["FP"], $offset);
        }

        $this->visit($ctx->block());
        $this->code->li($this->r["A0"], 0);
        $this->code->b($this->currentFunctionReturnLabel);

        $this->code->label($this->currentFunctionReturnLabel);
        $this->code->comment("Epilogo de funcion: restaurar SP, FP y RA");
        $this->code->mov($this->r["SP"], $this->r["FP"]);
        $this->code->ldpPost($this->r["FP"], $this->r["RA"], $this->r["SP"], 16);
        $this->code->ret();

        $this->env = $savedEnv;
        $this->stackOffset = $savedStackOffset;
        $this->loopLabels = $savedLoopLabels;
        $this->inFunction = $savedInFunction;
        $this->currentFunctionReturnLabel = $savedReturnLabel;
        $this->currentBaseReg = $savedBaseReg;
    }

    public function visitProgram(ProgramContext $ctx) {
        $this->code->comment("Configurando el frame pointer");
        $this->code->mov($this->r["FP"], $this->r["SP"]);
        $this->code->comment("Inicializando heap pointer y limite de heap");
        $this->code->ldrl($this->r["HP"], "heap_base");
        $this->code->ldrl($this->r["HEAP_END"], "heap_end");

        foreach ($ctx->stmt() as $stmt) {
            if ($stmt instanceof FunctionDeclarationContext) {
                $this->visit($stmt);
                continue;
            }
            $this->visit($stmt);
        }

        $this->code->endProgram();
        $this->code->emitRuntimeErrorHandlers(
            CompilerError::PANIC_OOB_LABEL,
            CompilerError::PANIC_OOB_EXIT_CODE,
            CompilerError::PANIC_OOM_LABEL,
            CompilerError::PANIC_OOM_EXIT_CODE,
            $this->r["A0"],
            $this->r["SYS"]
        );

        foreach ($this->foreignFunctions as $descriptor) {
            $this->emitForeignFunction($descriptor);
        }

        foreach ($this->natives as $descriptor) {
            $label = $descriptor["label"];
            if (!array_key_exists($label, $this->usedNativeLabels)) {
                continue;
            }
            $emitter = $descriptor["emitter"];
            $this->code->$emitter($label);
        }
        return $this->code;
    }

    public function visitPrintStatement(PrintStatementContext $ctx) {
        $type = $this->visit($ctx->e());
        if (!TypeChecker::isIntOrBoolType($type)) {
            $this->fail("print solo admite int o bool, se obtuvo " . TypeChecker::typeToString($type));
        }

        $this->code->comment("Imprimiendo el resultado de la expresion");
        $this->code->pop($this->r["A0"]);
        $this->code->printInt($this->r["A0"]);
    }

    public function visitVarDeclaration(VarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();
        $type = $this->visit($ctx->e());

        $this->stackOffset -= 8;
        $offset = $this->stackOffset;

        $this->env->set($varName, [
            "type" => $type,
            "offset" => $offset,
            "baseReg" => $this->currentBaseReg
        ]);

        $this->code->comment("Declaracion de variable: " . $varName . " (" . TypeChecker::typeToString($type) . ") en [FP, #" . $offset . "]");
        $this->code->pop($this->r["T0"]);
        $this->code->subi($this->r["SP"], $this->r["SP"], 8);
        $this->code->str($this->r["T0"], $this->r["FP"], $offset);
    }

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $exprType = $this->visit($ctx->e());

        $symbol = $this->env->get($varName);
        $offset = $symbol["offset"];

        if (!TypeChecker::typeEquals($exprType, $symbol["type"])) {
            $this->fail("No se puede asignar tipo " . TypeChecker::typeToString($exprType) . " a variable '" . $varName . "' de tipo " . TypeChecker::typeToString($symbol["type"]));
        }

        $baseReg = $this->symbolBaseReg($symbol);
        $this->code->comment("Asignacion a variable: " . $varName . " en [base, #" . $offset . "]");
        $this->code->pop($this->r["T0"]);
        $this->symbolAccess->storeScalarFromT0($symbol, $baseReg);
    }

    public function visitIfStatement(IfStatementContext $ctx) {
        $condType = $this->visit($ctx->e());
        if (!TypeChecker::isIntOrBoolType($condType)) {
            $this->fail("La condicion del 'if' debe ser de tipo int o bool, se obtuvo " . TypeChecker::typeToString($condType));
        }

        $this->code->comment("Evaluando condicion del if");
        $this->code->pop($this->r["T0"]);

        if ($ctx->else() !== null) {
            $elseLabel = $this->newLabel();
            $endLabel = $this->newLabel();

            $this->code->cbz($this->r["T0"], $elseLabel);
            $this->visit($ctx->block());
            $this->code->b($endLabel);
            $this->code->label($elseLabel);
            $this->visit($ctx->else());
            $this->code->label($endLabel);
            return;
        }

        $endLabel = $this->newLabel();
        $this->code->cbz($this->r["T0"], $endLabel);
        $this->visit($ctx->block());
        $this->code->label($endLabel);
    }

    public function visitWhileStatement(WhileStatementContext $ctx) {
        $startLabel = $this->newLabel();
        $endLabel = $this->newLabel();
        $this->loopLabels[] = ["start" => $startLabel, "end" => $endLabel];

        $this->code->label($startLabel);

        $condType = $this->visit($ctx->e());
        if (!TypeChecker::isIntOrBoolType($condType)) {
            $this->fail("La condicion del 'while' debe ser de tipo int o bool, se obtuvo " . TypeChecker::typeToString($condType));
        }

        $this->code->pop($this->r["T0"]);
        $this->code->cbz($this->r["T0"], $endLabel);
        $flow = $this->visit($ctx->block());
        if ($flow instanceof ReturnType) {
            array_pop($this->loopLabels);
            return $flow;
        }

        $this->code->b($startLabel);
        $this->code->label($endLabel);
        array_pop($this->loopLabels);
        return null;
    }

    public function visitContinueStatement(ContinueStatementContext $ctx) {
        if (empty($this->loopLabels)) {
            $this->fail("'continue' fuera de un ciclo while");
        }

        $loop = end($this->loopLabels);
        $this->code->b($loop["start"]);
        return new ContinueType();
    }

    public function visitBreakStatement(BreakStatementContext $ctx) {
        if (empty($this->loopLabels)) {
            $this->fail("'break' fuera de un ciclo while");
        }

        $loop = end($this->loopLabels);
        $this->code->b($loop["end"]);
        return new BreakType();
    }

    public function visitReturnStatement(ReturnStatementContext $ctx) {
        if (!$this->inFunction) {
            $this->fail("'return' fuera de una funcion");
        }

        if ($ctx->e() !== null) {
            $retType = $this->visit($ctx->e());
            if (!TypeChecker::isIntType($retType)) {
                $this->fail("En esta etapa, return solo admite int");
            }
            $this->code->pop($this->r["A0"]);
        } else {
            $this->code->li($this->r["A0"], 0);
        }

        $this->code->b($this->currentFunctionReturnLabel);
        return new ReturnType("int");
    }

    public function visitFunctionDeclaration(FunctionDeclarationContext $ctx) {
        $name = $ctx->ID()->getText();
        $params = [];
        if ($ctx->params() !== null) {
            $params = $this->visit($ctx->params());
        }

        if (count($params) > 8) {
            $this->fail("Funcion '" . $name . "' supera el maximo soportado de 8 parametros");
        }

        $analysis = $this->paramAnalyzer->analyze($ctx, $params);
        $argTypes = $analysis["argTypes"];
        $argModes = $analysis["argModes"];

        $label = "_fn_" . $name;
        $descriptor = [
            "kind" => "foreign_fn",
            "name" => $name,
            "label" => $label,
            "arity" => count($params),
            "argTypes" => $argTypes,
            "argModes" => $argModes,
            "returnType" => "int",
            "params" => $params,
            "closureEnv" => $this->env,
            "ctx" => $ctx
        ];

        $this->env->set($name, $descriptor);
        $this->foreignFunctions[] = $descriptor;
        return null;
    }

    public function visitFunctionCallStatement(FunctionCallStatementContext $ctx) {
        $fnName = $ctx->ID()->getText();
        $this->compileCall($fnName, $ctx->args(), false);
        return null;
    }

    public function visitArrayAssignmentStatement(ArrayAssignmentStatementContext $ctx) {
        $refInfo = $this->visit($ctx->ref_list());
        if (!is_array($refInfo)
            || !array_key_exists("id", $refInfo)
            || !array_key_exists("rank", $refInfo)) {
            $this->fail("Referencia a array invalida");
        }

        $varName = $refInfo["id"];
        $symbol = $this->env->get($varName);

        $assignExpr = ($ctx->assign !== null) ? $ctx->assign : $ctx->e();
        if ($assignExpr === null) {
            $this->fail("Asignacion a array invalida");
        }

        $assignType = $this->visit($assignExpr);

        $currentType = $symbol["type"];
        if (!TypeChecker::isArrayType($currentType)) {
            $this->fail("Se intento indexar un valor no-array en '" . $varName . "'");
        }

        $rank = array_key_exists("rank", $currentType) ? $currentType["rank"] : 1;
        if ($refInfo["rank"] !== $rank) {
            $this->fail("Se esperaban " . $rank . " indices para asignar en '" . $varName . "', se recibieron " . $refInfo["rank"]);
        }

        $expectedType = $currentType["elem"];
        if (!TypeChecker::typeEquals($assignType, $expectedType)) {
            $this->fail("Tipo incompatible en asignacion de array, se esperaba " . TypeChecker::typeToString($expectedType) . " y se obtuvo " . TypeChecker::typeToString($assignType));
        }

        $this->code->pop($this->r["T3"]);
        $this->code->pop($this->r["T4"]);
        $baseReg = $this->symbolBaseReg($symbol);
        $this->code->comment("Array assignment: cargando puntero base del array destino");
        $this->symbolAccess->loadArrayPointerToT1($symbol, $baseReg);

        $headerBytes = ($rank + 1) * 8;
        $this->code->comment("Array assignment: calculando direccion lineal del elemento");
        $this->code->li($this->r["T2"], 8);
        $this->code->mul($this->r["T4"], $this->r["T4"], $this->r["T2"]);
        $this->code->add($this->r["T2"], $this->r["T1"], $this->r["T4"]);
        $this->code->addi($this->r["T2"], $this->r["T2"], $headerBytes);
        $this->code->comment("Array assignment: escribiendo valor en heap");
        $this->code->str($this->r["T3"], $this->r["T2"], 0);
        return null;
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $prevOffset = $this->stackOffset;
        $this->env = new Environment($prevEnv);

        foreach ($ctx->stmt() as $stmt) {
            $flow = $this->visit($stmt);
            if ($flow instanceof FlowType) {
                if ($this->stackOffset !== $prevOffset) {
                    $bytesToReclaim = $prevOffset - $this->stackOffset;
                    $this->code->addi($this->r["SP"], $this->r["SP"], $bytesToReclaim);
                }
                $this->stackOffset = $prevOffset;
                $this->env = $prevEnv;
                return $flow;
            }
        }

        if ($this->stackOffset !== $prevOffset) {
            $bytesToReclaim = $prevOffset - $this->stackOffset;
            $this->code->addi($this->r["SP"], $this->r["SP"], $bytesToReclaim);
        }

        $this->stackOffset = $prevOffset;
        $this->env = $prevEnv;
        return null;
    }

    public function visitElse(ElseContext $ctx) {
        return $this->visit($ctx->block());
    }

    public function visitE(EContext $ctx) {
        return $this->visit($ctx->eq());
    }

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        $leftType = $this->visit($ctx->left);
        if ($ctx->right === null) {
            return $leftType;
        }

        $rightType = $this->visit($ctx->right);
        if (!TypeChecker::typeEquals($leftType, $rightType)) {
            $this->fail("Operador '==' requiere operandos del mismo tipo, se obtuvo " . TypeChecker::typeToString($leftType) . " y " . TypeChecker::typeToString($rightType));
        }

        $this->code->pop($this->r["T0"]);
        $this->code->pop($this->r["T1"]);
        $this->code->cmp($this->r["T1"], $this->r["T0"]);
        $this->code->cset($this->r["T0"], "eq");
        $this->code->push($this->r["T0"]);
        return "bool";
    }

    public function visitInequalityExpression(InequalityExpressionContext $ctx) {
        $leftType = $this->visit($ctx->left);
        if ($ctx->right === null) {
            return $leftType;
        }

        $rightType = $this->visit($ctx->right);
        $op = $ctx->op->getText();

        if (!TypeChecker::isIntType($leftType) || !TypeChecker::isIntType($rightType)) {
            $this->fail("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . TypeChecker::typeToString($leftType) . " y " . TypeChecker::typeToString($rightType));
        }

        $this->code->pop($this->r["T0"]);
        $this->code->pop($this->r["T1"]);
        $this->code->cmp($this->r["T1"], $this->r["T0"]);
        $cond = ($op === ">") ? "gt" : "lt";
        $this->code->cset($this->r["T0"], $cond);
        $this->code->push($this->r["T0"]);
        return "bool";
    }

    public function visitAddExpression(AddExpressionContext $ctx) {
        if ($ctx->add() === null) {
            return $this->visit($ctx->prod());
        }

        $leftType = $this->visit($ctx->add());
        $rightType = $this->visit($ctx->prod());
        $op = $ctx->op->getText();

        if (!TypeChecker::isIntType($leftType) || !TypeChecker::isIntType($rightType)) {
            $this->fail("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . TypeChecker::typeToString($leftType) . " y " . TypeChecker::typeToString($rightType));
        }

        $this->code->pop($this->r["T0"]);
        $this->code->pop($this->r["T1"]);

        if ($op === "+") {
            $this->code->add($this->r["T0"], $this->r["T1"], $this->r["T0"]);
        } elseif ($op === "-") {
            $this->code->sub($this->r["T0"], $this->r["T1"], $this->r["T0"]);
        } else {
            $this->fail("Operador desconocido: " . $op);
        }

        $this->code->push($this->r["T0"]);
        return "int";
    }

    public function visitProductExpression(ProductExpressionContext $ctx) {
        if ($ctx->prod() === null) {
            return $this->visit($ctx->unary());
        }

        $leftType = $this->visit($ctx->prod());
        $rightType = $this->visit($ctx->unary());
        $op = $ctx->op->getText();

        if (!TypeChecker::isIntType($leftType) || !TypeChecker::isIntType($rightType)) {
            $this->fail("Operador '" . $op . "' requiere operandos de tipo int, se obtuvo " . TypeChecker::typeToString($leftType) . " y " . TypeChecker::typeToString($rightType));
        }

        $this->code->pop($this->r["T0"]);
        $this->code->pop($this->r["T1"]);

        if ($op === "*") {
            $this->code->mul($this->r["T0"], $this->r["T1"], $this->r["T0"]);
        } elseif ($op === "/") {
            $this->code->div($this->r["T0"], $this->r["T1"], $this->r["T0"]);
        } else {
            $this->fail("Operador desconocido: " . $op);
        }

        $this->code->push($this->r["T0"]);
        return "int";
    }

    public function visitPrimaryExpression(PrimaryExpressionContext $ctx) {
        return $this->visit($ctx->primary());
    }

    public function visitUnaryExpression(UnaryExpressionContext $ctx) {
        $type = $this->visit($ctx->unary());
        if (!TypeChecker::isIntType($type)) {
            $this->fail("Operador '-' (unario) requiere operando de tipo int, se obtuvo " . TypeChecker::typeToString($type));
        }

        $this->code->pop($this->r["T0"]);
        $this->code->sub($this->r["T0"], $this->r["ZERO"], $this->r["T0"]);
        $this->code->push($this->r["T0"]);
        return "int";
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->e());
    }

    public function visitIntExpression(IntExpressionContext $ctx) {
        $number = intval($ctx->INT()->getText());
        $this->code->li($this->r["T0"], $number);
        $this->code->push($this->r["T0"]);
        return "int";
    }

    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        $symbol = $this->env->get($varName);
        if (!is_array($symbol)
            || !array_key_exists("offset", $symbol)) {
            $this->fail("'" . $varName . "' no es una variable escalar");
        }
        $offset = $symbol["offset"];

        $baseReg = $this->symbolBaseReg($symbol);
        $this->symbolAccess->loadScalarValueToT0($symbol, $baseReg);
        $this->code->push($this->r["T0"]);
        return $symbol["type"];
    }

    public function visitBoolExpression(BoolExpressionContext $ctx) {
        $value = $ctx->bool->getText();
        $intVal = ($value === "true") ? 1 : 0;

        $this->code->li($this->r["T0"], $intVal);
        $this->code->push($this->r["T0"]);
        return "bool";
    }

    public function visitFunctionCallExpression(FunctionCallExpressionContext $ctx) {
        $fnName = $ctx->ID()->getText();
        return $this->compileCall($fnName, $ctx->args(), true);
    }

    public function visitArrayExpression(ArrayExpressionContext $ctx) {
        return $this->visit($ctx->array());
    }

    public function visitArrayAccessExpression(ArrayAccessExpressionContext $ctx) {
        $refInfo = $this->visit($ctx->ref_list());
        if (!is_array($refInfo)
            || !array_key_exists("id", $refInfo)
            || !array_key_exists("rank", $refInfo)) {
            $this->fail("Referencia a array invalida");
        }

        $varName = $refInfo["id"];
        $symbol = $this->env->get($varName);

        $currentType = $symbol["type"];
        if (!TypeChecker::isArrayType($currentType)) {
            $this->fail("Se intento indexar un valor no-array en '" . $varName . "'");
        }

        $rank = array_key_exists("rank", $currentType) ? $currentType["rank"] : 1;
        if ($refInfo["rank"] !== $rank) {
            $this->fail("Se esperaban " . $rank . " indices para '" . $varName . "', se recibieron " . $refInfo["rank"]);
        }

        $baseReg = $this->symbolBaseReg($symbol);
        $this->code->comment("Array access: cargando puntero base del array");
        $this->symbolAccess->loadArrayPointerToT1($symbol, $baseReg);
        $this->code->pop($this->r["T3"]);

        $headerBytes = ($rank + 1) * 8;
        $this->code->comment("Array access: calculando direccion lineal del elemento");
        $this->code->li($this->r["T2"], 8);
        $this->code->mul($this->r["T3"], $this->r["T3"], $this->r["T2"]);
        $this->code->add($this->r["T2"], $this->r["T1"], $this->r["T3"]);
        $this->code->addi($this->r["T2"], $this->r["T2"], $headerBytes);

        $this->code->comment("Array access: leyendo valor desde heap");
        $this->code->ldr($this->r["T3"], $this->r["T2"], 0);
        $this->code->push($this->r["T3"]);
        return $currentType["elem"];
    }

    public function visitTransformRowMajor(TransformRowMajorContext $ctx) {
        if ($this->collectArrayLiteral) {
            $listInfo = $this->visit($ctx->exp_list());
            if (!is_array($listInfo)
                || !array_key_exists("node", $listInfo)
                || $listInfo["node"] !== "expr_list") {
                $this->fail("Array literal invalido: no se encontro metadata de lista");
            }
            return [
                "node" => "array_literal",
                "elemType" => $listInfo["elemType"],
                "dims" => $listInfo["dims"],
                "totalElements" => $listInfo["totalElements"]
            ];
        }

        $this->collectArrayLiteral = true;
        $listInfo = $this->visit($ctx->exp_list());
        $this->collectArrayLiteral = false;

        if (!is_array($listInfo)
            || !array_key_exists("node", $listInfo)
            || $listInfo["node"] !== "expr_list") {
            $this->fail("Array literal invalido: no se encontro metadata de lista");
        }

        $elemType = $listInfo["elemType"];
        $dims = $listInfo["dims"];
        $totalElements = $listInfo["totalElements"];
        $rank = count($dims);

        $headerBytes = ($rank + 1) * 8;
        $bytes = $headerBytes + ($totalElements * 8);

        $this->code->comment("Array literal: reservando bloque continuo en heap");
        $this->code->emitHeapAllocFixed(
            $bytes,
            $this->r["T4"],
            $this->r["HP"],
            $this->r["HEAP_END"],
            $this->r["T0"],
            $this->r["T1"],
            CompilerError::PANIC_OOM_LABEL
        );

        $this->code->comment("Array literal: escribiendo header (rank y dimensiones)");
        $this->code->li($this->r["T0"], $rank);
        $this->code->str($this->r["T0"], $this->r["T4"], 0);
        for ($i = 0; $i < $rank; $i++) {
            $this->code->li($this->r["T0"], $dims[$i]);
            $this->code->str($this->r["T0"], $this->r["T4"], 8 + ($i * 8));
        }

        $this->code->comment("Array literal: escribiendo elementos en orden row-major");
        for ($i = $totalElements - 1; $i >= 0; $i--) {
            $offset = $headerBytes + ($i * 8);
            $this->code->pop($this->r["T0"]);
            $this->code->str($this->r["T0"], $this->r["T4"], $offset);
        }

        $this->code->push($this->r["T4"]);
        return TypeChecker::makeArrayTypeWithRankAndDims($elemType, $rank, $dims);
    }

    public function visitInitExpressionList(InitExpressionListContext $ctx) {
        if (!$this->collectArrayLiteral) {
            $this->fail("exp_list solo puede evaluarse en contexto de array literal");
        }

        $first = $this->visit($ctx->e());

        if (TypeChecker::isIntOrBoolType($first)) {
            return [
                "node" => "expr_list",
                "kind" => "scalar",
                "elemType" => $first,
                "dims" => [1],
                "totalElements" => 1
            ];
        }

        if (is_array($first)
            && array_key_exists("node", $first)
            && $first["node"] === "array_literal") {
            if (!array_key_exists("dims", $first)
                || !array_key_exists("elemType", $first)
                || !array_key_exists("totalElements", $first)) {
                $this->fail("Array literal anidado invalido");
            }

            return [
                "node" => "expr_list",
                "kind" => "array",
                "elemType" => $first["elemType"],
                "dims" => array_merge([1], $first["dims"]),
                "totalElements" => $first["totalElements"]
            ];
        }

        $this->fail("Array literal solo admite escalares int/bool o sub-arrays literales");
    }

    public function visitExpressionList(ExpressionListContext $ctx) {
        $left = $this->visit($ctx->exp_list());

        if (!$this->collectArrayLiteral || !is_array($left)
            || !array_key_exists("node", $left)
            || $left["node"] !== "expr_list") {
            $this->fail("Metadata invalida en exp_list");
        }

        $next = $this->visit($ctx->e());
        $result = $left;

        if (TypeChecker::isIntOrBoolType($next)) {
            if ($result["kind"] !== "scalar") {
                $this->fail("Array multidimensional debe ser rectangular y no mezclar escalares con sub-arrays");
            }

            if (!TypeChecker::typeEquals($result["elemType"], $next)) {
                $this->fail("Array literal requiere elementos homogeneos, se obtuvo " . TypeChecker::typeToString($result["elemType"]) . " y " . TypeChecker::typeToString($next));
            }

            $result["dims"][0] = $result["dims"][0] + 1;
            $result["totalElements"] = $result["totalElements"] + 1;
            return $result;
        }

        if (is_array($next)
            && array_key_exists("node", $next)
            && $next["node"] === "array_literal") {
            if ($result["kind"] !== "array") {
                $this->fail("Array multidimensional debe ser rectangular y no mezclar escalares con sub-arrays");
            }

            if (!TypeChecker::typeEquals($result["elemType"], $next["elemType"])) {
                $this->fail("Sub-arrays deben tener el mismo tipo escalar base");
            }

            $expectedChildDims = array_slice($result["dims"], 1);
            if ($expectedChildDims !== $next["dims"]) {
                $this->fail("Array multidimensional debe ser rectangular (sub-arrays con mismas dimensiones)");
            }

            $result["dims"][0] = $result["dims"][0] + 1;
            $result["totalElements"] = $result["totalElements"] + $next["totalElements"];
            return $result;
        }

        $this->fail("Array literal solo admite escalares int/bool o sub-arrays literales");
    }

    public function visitInitArrayReferenceExpression(InitArrayReferenceExpressionContext $ctx) {
        $id = $ctx->ID()->getText();
        $symbol = $this->env->get($id);
        $currentType = $symbol["type"];
        if (!TypeChecker::isArrayType($currentType)) {
            $this->fail("Se intento indexar un valor no-array en '" . $id . "'");
        }

        $indexType = $this->visit($ctx->e());
        if (!TypeChecker::isIntType($indexType)) {
            $this->fail("El indice de array debe ser int, se obtuvo " . TypeChecker::typeToString($indexType));
        }

        $this->code->pop($this->r["T0"]);
        $baseReg = $this->symbolBaseReg($symbol);
        $this->code->comment("Array index rank-1: bounds check sobre dimension 0");
        $this->symbolAccess->loadArrayPointerToT1($symbol, $baseReg);
        $this->code->cmp($this->r["T0"], "#0");
        $this->code->bcond("lt", CompilerError::PANIC_OOB_LABEL);
        $this->code->ldr($this->r["T2"], $this->r["T1"], 8);
        $this->code->cmp($this->r["T0"], $this->r["T2"]);
        $this->code->bcond("ge", CompilerError::PANIC_OOB_LABEL);
        $this->code->push($this->r["T0"]);

        return [
            "id" => $id,
            "rank" => 1
        ];
    }

    public function visitArrayReferenceExpression(ArrayReferenceExpressionContext $ctx) {
        $left = $this->visit($ctx->ref_list());
        if (!is_array($left)
            || !array_key_exists("id", $left)
            || !array_key_exists("rank", $left)) {
            $this->fail("Referencia a array invalida");
        }

        $symbol = $this->env->get($left["id"]);
        $currentType = $symbol["type"];
        if (!TypeChecker::isArrayType($currentType)) {
            $this->fail("Se intento indexar un valor no-array en '" . $left["id"] . "'");
        }

        $expectedRank = array_key_exists("rank", $currentType) ? $currentType["rank"] : 1;
        if ($left["rank"] >= $expectedRank) {
            $this->fail("Se proporcionaron demasiados indices para '" . $left["id"] . "'");
        }

        $indexType = $this->visit($ctx->e());
        if (!TypeChecker::isIntType($indexType)) {
            $this->fail("El indice de array debe ser int, se obtuvo " . TypeChecker::typeToString($indexType));
        }

        $this->code->pop($this->r["T0"]);
        $baseReg = $this->symbolBaseReg($symbol);
        $this->code->comment("Array index rank>1: bounds check sobre dimension actual");
        $this->symbolAccess->loadArrayPointerToT1($symbol, $baseReg);
        $dimOffset = 8 + ($left["rank"] * 8);
        $this->code->cmp($this->r["T0"], "#0");
        $this->code->bcond("lt", CompilerError::PANIC_OOB_LABEL);
        $this->code->ldr($this->r["T2"], $this->r["T1"], $dimOffset);
        $this->code->cmp($this->r["T0"], $this->r["T2"]);
        $this->code->bcond("ge", CompilerError::PANIC_OOB_LABEL);

        $this->code->pop($this->r["T3"]);
        $this->code->comment("Array index rank>1: acumulando indice lineal row-major");
        $this->code->mul($this->r["T3"], $this->r["T3"], $this->r["T2"]);
        $this->code->add($this->r["T3"], $this->r["T3"], $this->r["T0"]);
        $this->code->push($this->r["T3"]);

        return [
            "id" => $left["id"],
            "rank" => $left["rank"] + 1
        ];
    }

    public function visitParameterList(ParameterListContext $ctx) {
        $params = [];
        foreach ($ctx->ID() as $id) {
            $params[] = $id->getText();
        }
        return $params;
    }

    public function visitArgumentList(ArgumentListContext $ctx) {
        $args = [];
        foreach ($ctx->e() as $expr) {
            $args[] = $expr;
        }
        return $args;
    }
}
