<?php

namespace App\Ast;

use Context\VarDeclarationContext;

trait Declaracion {

    public function visitVarDeclaration(VarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->expresion());
        $this->env->set($varName, $value);
        return $value;
    }
}