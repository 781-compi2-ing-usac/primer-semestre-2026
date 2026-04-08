<?php

namespace App\Ast\Expresiones;

use Context\RelacionalExpresionContext;
use Context\EqualityExpressionContext;
use Context\AndExpressionContext;
use Context\OrExpressionContext;
use Context\NotExpressionContext;
use Context\BoolTrueExpressionContext;
use Context\BoolFalseExpressionContext;
use App\Env\Result;

trait Booleanas
{
    public function visitRelacionalExpresion(RelacionalExpresionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));
        
        $op = $ctx->op->getText();
        $this->asmGenerador->comment("Visitando expresión relacional: " . $op);

        $rightReg = $this->stack->popValue();
        $leftReg = $this->stack->popValue();

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
            default:
                throw new \Exception("Operador relacional desconocido: " . $op);
        }

        $this->stack->pushValue("x9");

        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));
        
        $op = $ctx->op->getText();
        $this->asmGenerador->comment("Visitando expresión de igualdad: " . $op);

        $rightReg = $this->stack->popValue();
        $leftReg = $this->stack->popValue();

        $this->asmGenerador->cmp($leftReg, $rightReg);

        if ($op == "==") {
            $this->asmGenerador->cset("x9", "eq");
        } else {
            $this->asmGenerador->cset("x9", "ne");
        }

        $this->stack->pushValue("x9");

        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitAndExpression(AndExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));

        $this->asmGenerador->comment("Visitando expresión AND");

        $rightReg = $this->stack->popValue();
        $leftReg = $this->stack->popValue();
        
        $this->asmGenerador->and_op("x9", $leftReg, $rightReg);
        
        $this->stack->pushValue("x9");

        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitOrExpression(OrExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));

        $this->asmGenerador->comment("Visitando expresión OR");

        $rightReg = $this->stack->popValue();
        $leftReg = $this->stack->popValue();
        
        $this->asmGenerador->orr("x9", $leftReg, $rightReg);
        
        $this->stack->pushValue("x9");

        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitNotExpression(NotExpressionContext $ctx) {
        $operandResult = $this->visit($ctx->expresion());

        $this->asmGenerador->comment("Visitando expresión NOT");
        
        $operandReg = $this->stack->popValue();
        
        $this->asmGenerador->mvn("x9", $operandReg);
        
        $this->stack->pushValue("x9");

        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitBoolTrueExpression(BoolTrueExpressionContext $ctx) {
        $this->asmGenerador->comment("Cargando true");
        $this->stack->pushImmediate(1);
        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitBoolFalseExpression(BoolFalseExpressionContext $ctx) {
        $this->asmGenerador->comment("Cargando false");
        $this->stack->pushImmediate(0);
        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }
}
