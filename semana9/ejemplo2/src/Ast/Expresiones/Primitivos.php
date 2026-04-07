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
        $number = intval($ctx->INT()->getText());
        $this->asmGenerador->comment("Cargando entero: " . $number);
        $this->stack->pushImmediate($number);
        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }

    public function visitFloatExpression(FloatExpressionContext $ctx) {
        $number = intval($ctx->FLOAT()->getText());
        $this->asmGenerador->comment("Cargando float: " . $number);
        $this->stack->pushImmediate($number);
        return Result::stack(Result::FLOAT, $this->stack->getStackOffset());
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
