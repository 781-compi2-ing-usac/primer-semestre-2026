<?php

namespace App\Ast\Expresiones;

use Context\IntExpressionContext;
use Context\{ReferenceExpressionContext,FloatExpresionContext};
use App\Env\{Symbol, Result};

trait Primitivos {

    public function visitIntExpression(IntExpressionContext $ctx) {
        return new Result(Result::INT, intval($ctx->INT()->getText()));
    }

    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        //convertir de Symbol a result
        return Symbol::asResult($this->env->get($varName));
    }

    public function visitFloatExpresion(FloatExpresionContext $ctx) {
        return new Result(Result::FLOAT, (Float) $ctx->FLOAT()->getText());
    }
}