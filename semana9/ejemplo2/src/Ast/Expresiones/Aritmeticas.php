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
        $this->asmGenerador->comment("Aritmetica: " . $op);

        $this->regs->pop();
        $this->asmGenerador->ldr("x1", "sp");
        $this->regs->pop();
        $this->asmGenerador->add("x0", "x1", "x0");
        $this->regs->push(0);

        return Result::stack(Result::INT, 0);
    }

    public function visitNegacionExpression(NegacionExpressionContext $ctx) {
        $operandResult = $this->visit($ctx->expresion());
        
        $this->asmGenerador->comment("Negacion");
        $this->regs->pop();
        $this->asmGenerador->mvn("x0", "x0");
        $this->regs->push(0);
        
        return Result::stack(Result::INT, 0);
    }
}
