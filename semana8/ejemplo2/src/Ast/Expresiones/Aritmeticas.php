<?php

namespace App\Ast\Expresiones;

use Context\AritmeticaExpressionContext;
use Context\NegacionExpressionContext;
use App\Env\Result;

trait Aritmeticas
{

    public function visitAritmeticaExpression(AritmeticaExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));
        
        $op = $ctx->op->getText();
        $this->asmGenerador->comment("Visitando expresión aritmética: " . $op);

        $leftReg = $this->regs->getValueFromResult($leftResult);
        $rightReg = $this->regs->getValueFromResult($rightResult);

        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);

        switch ($op) {
            case '+':
                $this->asmGenerador->add($resultReg->valor, $leftReg, $rightReg);
                break;
            case '-':
                $this->asmGenerador->sub($resultReg->valor, $leftReg, $rightReg);
                break;
            case '*':
                $this->asmGenerador->mul($resultReg->valor, $leftReg, $rightReg);
                break;
            case '/':
                $this->asmGenerador->div($resultReg->valor, $leftReg, $rightReg);
                break;
            default:
                throw new \Exception("Operador desconocido: " . $op);
        }

        $this->regs->freeRegister($leftResult);
        $this->regs->freeRegister($rightResult);

        return $resultReg;
    }

    public function visitNegacionExpression(NegacionExpressionContext $ctx) {
        $operandResult = $this->visit($ctx->expresion());
        
        $this->asmGenerador->comment("Visitando expresión de negación unaria");
        
        $operandReg = $this->regs->getValueFromResult($operandResult);
        $zeroReg = $this->regs->getZeroRegister();
        
        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);
        
        $this->asmGenerador->sub($resultReg->valor, $zeroReg->name, $operandReg);
        
        $this->regs->freeRegister($operandResult);

        return $resultReg;
    }
}
