<?php

use Context\AddContext;
use Context\ProductContext;
use Context\ParenContext;
use Context\IntContext;

class Interpreter extends GrammarBaseVisitor {
    public function visitAdd(AddContext $ctx){
        $left = $this->visit($ctx->e(0));
        $right = $this->visit($ctx->t(1));
        return $left + $right;
    }

    public function visitProduct(ProductContext $ctx){
        $left = $this->visit($ctx->t());
        $right = $this->visit($ctx->f());
        return $left * $right;
    }

    public function visitParen(ParenContext $ctx){
        return $this->visit($ctx->e());
    }

    public function visitInt(IntContext $ctx){
        return intval($ctx->DIGIT()->getText());
    }
}