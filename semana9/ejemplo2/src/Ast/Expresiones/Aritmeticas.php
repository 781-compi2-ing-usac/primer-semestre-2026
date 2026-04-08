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

        $rightReg = $this->stack->popValue();
        $leftReg = $this->stack->popValue();

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
            default:
                throw new \Exception("Operador desconocido: " . $op);
        }

        $this->stack->pushValue("x9");

        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitNegacionExpression(NegacionExpressionContext $ctx) {
        $operandResult = $this->visit($ctx->expresion());
        
        $this->asmGenerador->comment("Visitando expresión de negación unaria");
        
        $operandReg = $this->stack->popValue();
        
        $this->asmGenerador->sub("x9", "xzr", $operandReg);
        
        $this->stack->pushValue("x9");

        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }
}
