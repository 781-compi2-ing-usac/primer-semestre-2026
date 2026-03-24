<?php

namespace App\Ast\Expresiones;

use Context\IntExpressionContext;
use Context\FloatExpressionContext;
use Context\ReferenceExpressionContext;
use Context\GroupedExpressionContext;
use Context\PrimitivoExpressionContext;
use App\Env\Result;

trait Primitivos
{
    public function visitIntExpression(IntExpressionContext $ctx) {
        $number = intval($ctx->INT()->getText());
        $this->asmGenerador->comment("Int: " . $number);
        return $this->regs->push($number);
    }

    public function visitFloatExpression(FloatExpressionContext $ctx) {
        $number = (int)floatval($ctx->FLOAT()->getText());
        $this->asmGenerador->comment("Float: " . $number);
        return $this->regs->push($number);
    }

    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $id = $ctx->ID()->getText();
        $this->asmGenerador->comment("Ref: " . $id);
        return Result::buildVacio();
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->expresion());
    }

    public function visitPrimitivoExpression(PrimitivoExpressionContext $ctx) {
        return $this->visit($ctx->primary());
    }
}
