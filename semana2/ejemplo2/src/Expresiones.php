<?php 

use Context\UnaryExpressionContext;

trait ExpresionesHandler {

    public function visitUnaryExpression(UnaryExpressionContext $ctx) {     
        return - $this->visit($ctx->e());
    }

}