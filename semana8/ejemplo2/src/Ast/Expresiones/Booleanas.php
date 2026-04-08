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

        $leftReg = $this->regs->getValueFromResult($leftResult);
        $rightReg = $this->regs->getValueFromResult($rightResult);

        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);

        $this->asmGenerador->cmp($leftReg, $rightReg);

        switch ($op) {
            case '<':
                $this->asmGenerador->cset($resultReg->valor, "lt");
                break;
            case '<=':
                $this->asmGenerador->cset($resultReg->valor, "le");
                break;
            case '>':
                $this->asmGenerador->cset($resultReg->valor, "gt");
                break;
            case '>=':
                $this->asmGenerador->cset($resultReg->valor, "ge");
                break;
            default:
                throw new \Exception("Operador relacional desconocido: " . $op);
        }

        $this->regs->freeRegister($leftResult);
        $this->regs->freeRegister($rightResult);

        return $resultReg;
    }

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));
        
        $op = $ctx->op->getText();
        $this->asmGenerador->comment("Visitando expresión de igualdad: " . $op);

        $leftReg = $this->regs->getValueFromResult($leftResult);
        $rightReg = $this->regs->getValueFromResult($rightResult);

        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);

        $this->asmGenerador->cmp($leftReg, $rightReg);

        if ($op == "==") {
            $this->asmGenerador->cset($resultReg->valor, "eq");
        } else {
            $this->asmGenerador->cset($resultReg->valor, "ne");
        }

        $this->regs->freeRegister($leftResult);
        $this->regs->freeRegister($rightResult);

        return $resultReg;
    }

    public function visitAndExpression(AndExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));

        $this->asmGenerador->comment("Visitando expresión AND");

        $leftReg = $this->regs->getValueFromResult($leftResult);
        $rightReg = $this->regs->getValueFromResult($rightResult);

        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);
        
        $this->asmGenerador->and_op($resultReg->valor, $leftReg, $rightReg);
        
        $this->regs->freeRegister($leftResult);
        $this->regs->freeRegister($rightResult);

        return $resultReg;
    }

    public function visitOrExpression(OrExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));

        $this->asmGenerador->comment("Visitando expresión OR");

        $leftReg = $this->regs->getValueFromResult($leftResult);
        $rightReg = $this->regs->getValueFromResult($rightResult);

        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);
        
        $this->asmGenerador->orr($resultReg->valor, $leftReg, $rightReg);
        
        $this->regs->freeRegister($leftResult);
        $this->regs->freeRegister($rightResult);

        return $resultReg;
    }

    public function visitNotExpression(NotExpressionContext $ctx) {
        $operandResult = $this->visit($ctx->expresion());

        $this->asmGenerador->comment("Visitando expresión NOT");
        
        $operandReg = $this->regs->getValueFromResult($operandResult);
        
        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);
        
        $this->asmGenerador->mvn($resultReg->valor, $operandReg);
        
        $this->regs->freeRegister($operandResult);

        return $resultReg;
    }

    public function visitBoolTrueExpression(BoolTrueExpressionContext $ctx) {
        $this->asmGenerador->comment("Cargando true");
        
        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);
        $this->asmGenerador->li($resultReg->valor, 1);
        
        return $resultReg;
    }

    public function visitBoolFalseExpression(BoolFalseExpressionContext $ctx) {
        $this->asmGenerador->comment("Cargando false");
        
        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);
        $this->asmGenerador->li($resultReg->valor, 0);
        
        return $resultReg;
    }
}
