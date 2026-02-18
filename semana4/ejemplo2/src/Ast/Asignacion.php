<?php

namespace App\Ast;

use Context\AssignmentStatementContext;
use App\Env\{Result, Symbol};

trait Asignacion {

    //tabla de casteos

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $result = $this->visit($ctx->expresion());
        $symbol = $this->env->get($varName);

        if ($symbol->clase == Symbol::CLASE_VARIABLE && $result->tipo == $symbol->tipo) {
            $symbol->valor = $result->valor;
        } else {
            //casteos
            error_log("Hubo un error en la asignacion");
        }
        //$this->env->assign($varName, $value);
        return Result::buildVacio();
    }
}