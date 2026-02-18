<?php

namespace App\Ast;

use Context\VarDeclarationContext;
use App\Env\{Symbol, Result};

trait Declaracion {

    public function visitVarDeclaration(VarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();
        $varTipo = $ctx->tipos()->getText();
        $result = $this->visit($ctx->expresion());

        if ($varTipo == $result->tipo) {
            $symbol = new Symbol($result->tipo, $result->valor, Symbol::CLASE_VARIABLE, 0, 0);
            $this->env->set($varName, $symbol);
        } else {
            error_log("Los tipos no coinciden, variable no declarada");
        }
        return Result::buildVacio();
    }
}