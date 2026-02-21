<?php

namespace App\Ast\Expresiones;

use Context\IntExpressionContext;
use Context\BoolExpressionContext;

trait Primitivos {

    public function visitIntExpression(IntExpressionContext $ctx) {
        return intval($ctx->INT()->getText());
    }

    public function visitBoolExpression(BoolExpressionContext $ctx) {
        return $ctx->BOOLEAN()->getText() == "TRUE";
    }
}