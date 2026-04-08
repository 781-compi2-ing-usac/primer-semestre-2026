<?php

namespace App\Ast\Sentencias;

use Context\FunctionDeclarationContext;
use Context\BlockStatementContext;
use Context\ReturnStatementContext;
use Context\PrintStatementContext;
use Context\IfStatementContext;
use Context\IntExpressionContext;
use Context\AritmeticaExpressionContext;
use Context\RelacionalExpresionContext;
use Context\ElseContext;
use App\Env\Symbol;
use App\Env\Result;

class FunctionGenerator
{
    private $asmGenerador;
    private $stack;
    private $env;
    private $compiler;

    public function __construct($asmGenerador, $stack, $env, $compiler = null) {
        $this->asmGenerador = $asmGenerador;
        $this->stack = $stack;
        $this->env = $env;
        $this->compiler = $compiler;
    }

    public function generateFunction($funcName, $symbol) {
        $funcLabel = "func_" . $funcName;
        
        $this->asmGenerador->comment("=== Inicio de función: " . $funcName . " ===");
        $this->asmGenerador->label($funcLabel);
        
        $totalSlots = $symbol->totalSlots;
        $paramCount = $symbol->paramCount;
        $returnLabel = "func_end_" . $funcName;
        
        $this->asmGenerador->comment("Reservar espacio para registro de activación");
        $this->asmGenerador->subi("sp", "sp", ($totalSlots + 2) * 16);
        
        $this->asmGenerador->comment("Guardar FP y LR");
        $this->asmGenerador->str("x29", "sp", ($totalSlots + 1) * 16);
        $this->asmGenerador->str("x30", "sp", ($totalSlots + 0) * 16);
        
        $this->asmGenerador->comment("Actualizar FP");
        $this->asmGenerador->mov("x29", "sp");
        
        $paramBaseOffset = ($paramCount + 2) * 16;
        foreach ($symbol->params as $param) {
            $offset = $paramBaseOffset + ($param->position * 16);
            $this->asmGenerador->comment("Cargar parámetro: " . $param->id);
            $this->asmGenerador->ldr("x9", "x29", $offset);
            $this->asmGenerador->str("x9", "x29", -($param->position + 1) * 16);
        }
        
        $this->visitBlock($symbol->valor);
        
        $this->asmGenerador->label($returnLabel);
        
        $this->asmGenerador->comment("Restaurar LR y FP");
        $this->asmGenerador->ldr("x30", "x29", 0);
        $this->asmGenerador->ldr("x29", "x29", 16);
        
        $this->asmGenerador->comment("Restaurar SP");
        $this->asmGenerador->mov("sp", "x29");
        
        $this->asmGenerador->comment("Retornar");
        $this->asmGenerador->ret();
        
        $this->asmGenerador->comment("=== Fin de función: " . $funcName . " ===");
    }

    public function visitBlock($block) {
        foreach ($block->stmt() as $stmt) {
            $this->visit($stmt);
        }
    }

    public function visit($stmt) {
        if ($stmt instanceof ReturnStatementContext) {
            $this->handleReturn($stmt);
            return;
        }
        
        if ($stmt instanceof PrintStatementContext) {
            $this->handlePrint($stmt);
            return;
        }
        
        if ($stmt instanceof IfStatementContext) {
            $this->handleIf($stmt);
            return;
        }
        
        if ($stmt instanceof FunctionDeclarationContext) {
            return;
        }
    }

    public function handleReturn($ctx) {
        if ($ctx->expresion() !== null) {
            $result = $this->visitExpression($ctx->expresion());
            $returnReg = $this->stack->popValue();
            $this->asmGenerador->mov("x0", $returnReg);
        }
        $this->asmGenerador->ret();
    }

    public function handlePrint($ctx) {
        $result = $this->visitExpression($ctx->expresion());
        $reg = $this->stack->popValue();
        $this->asmGenerador->mov("x0", $reg);
        $this->asmGenerador->printInt();
    }

    public function handleIf($ctx) {
        $conditionResult = $this->visitExpression($ctx->expresion());
        $conditionReg = $this->stack->popValue();
        
        $endLabel = $this->asmGenerador->generateLabel("if_end");
        $elseLabel = $ctx->else() !== null ? $this->asmGenerador->generateLabel("if_else") : $endLabel;
        
        $this->asmGenerador->cmp($conditionReg, "xzr");
        
        if ($ctx->else() !== null) {
            $this->asmGenerador->beq($elseLabel);
        } else {
            $this->asmGenerador->beq($endLabel);
        }
        
        $this->visitBlock($ctx->block());
        
        if ($ctx->else() !== null) {
            $this->asmGenerador->b($endLabel);
            $this->asmGenerador->label($elseLabel);
            $this->visit($ctx->else());
        }
        
        $this->asmGenerador->label($endLabel);
    }

    public function visitExpression($expr) {
        if ($expr instanceof IntExpressionContext) {
            $value = intval($expr->INT()->getText());
            $this->stack->pushImmediate($value);
            return Result::stack(Result::INT, $this->stack->getStackOffset());
        }
        
        if ($expr instanceof AritmeticaExpressionContext) {
            return $this->handleAritmetica($expr);
        }
        
        if ($expr instanceof RelacionalExpresionContext) {
            return $this->handleRelacional($expr);
        }
        
        return Result::buildVacio();
    }

    private function handleAritmetica($ctx) {
        $leftResult = $this->visitExpression($ctx->expresion(0));
        $rightResult = $this->visitExpression($ctx->expresion(1));
        
        $rightReg = $this->stack->popValue();
        $leftReg = $this->stack->popValue();
        
        $op = $ctx->op->getText();
        
        switch ($op) {
            case '+':
                $this->asmGenerador->add("x9", $leftReg, $rightReg);
                break;
            case '-':
                $this->asmGenerador->sub("x9", $leftReg, $rightReg);
                break;
            case '*':
                $this->asmGenerador->mul("x9", $leftReg, $rightReg);
                break;
            case '/':
                $this->asmGenerador->div("x9", $leftReg, $rightReg);
                break;
        }
        
        $this->stack->pushValue("x9");
        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    private function handleRelacional($ctx) {
        $leftResult = $this->visitExpression($ctx->expresion(0));
        $rightResult = $this->visitExpression($ctx->expresion(1));
        
        $rightReg = $this->stack->popValue();
        $leftReg = $this->stack->popValue();
        
        $op = $ctx->op->getText();
        
        $this->asmGenerador->cmp($leftReg, $rightReg);
        
        switch ($op) {
            case '<':
                $this->asmGenerador->cset("x9", "lt");
                break;
            case '<=':
                $this->asmGenerador->cset("x9", "le");
                break;
            case '>':
                $this->asmGenerador->cset("x9", "gt");
                break;
            case '>=':
                $this->asmGenerador->cset("x9", "ge");
                break;
        }
        
        $this->stack->pushValue("x9");
        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }
}
