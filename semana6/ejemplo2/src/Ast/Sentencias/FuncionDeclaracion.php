<?php

namespace App\Ast\Sentencias;

use Context\FunctionDeclarationContext;
use App\Env\{Symbol, Result};

trait FuncionDeclaracion
{
    public function visitFunctionDeclaration(FunctionDeclarationContext $context)
	{
        $varName = $context->ID()->getText();
        $varTipo = $context->tipos()->getText();
        $params = $this->visit($context->params());
        $bloque = $context->block();

        $symbol = new Symbol($varTipo, $bloque, Symbol::CLASE_FUNCION, 0, 0);
        $symbol->params = $params;
        $this->env->set($varName, $symbol);
	    return Result::buildVacio();
	}
}