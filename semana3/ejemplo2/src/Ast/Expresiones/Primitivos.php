<?php

namespace App\Ast\Expresiones;

use Context\IntExpressionContext;

trait Primitivos {

    public function visitIntExpression(IntExpressionContext $ctx) {
        return intval($ctx->INT()->getText());
    }
}