<?php

namespace App\Ast\Expresiones;

use Context\IntExpressionContext;
use Context\FloatExpressionContext;
use Context\ReferenceExpressionContext;
use Context\GroupedExpressionContext;
use Context\PrimitivoExpressionContext;
use App\Env\Result;
use Instruction;

trait Primitivos
{
    public function visitIntExpression(IntExpressionContext $ctx) {
        $this->asmGenerador->comment("Cargando entero: " . $ctx->INT()->getText());
        
        $resultReg = $this->regs->allocateRegisterWithType(Result::INT);
        $number = intval($ctx->INT()->getText());
        $this->asmGenerador->li($resultReg->valor, $number);
        
        return $resultReg;
    }

    public function visitFloatExpression(FloatExpressionContext $ctx) {
        $this->asmGenerador->comment("Cargando float: " . $ctx->FLOAT()->getText());
        
        $resultReg = $this->regs->allocateRegisterWithType(Result::FLOAT);
        $number = floatval($ctx->FLOAT()->getText());
        $this->asmGenerador->li($resultReg->valor, (int)$number);
        
        return $resultReg;
    }

    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $id = $ctx->ID()->getText();
        $this->asmGenerador->comment("Referencia a variable: " . $id);
        return Result::buildVacio();
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->expresion());
    }

    public function visitPrimitivoExpression(PrimitivoExpressionContext $ctx) {
        return $this->visit($ctx->primary());
    }
}
