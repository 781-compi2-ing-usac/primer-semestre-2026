<?php

namespace App\Ast\Sentencias;

use Context\ReturnStatementContext;
use App\Env\Result;

trait Transferencia
{
    public function visitReturnStatement(ReturnStatementContext $context)
	{
        if ($context->expresion()) {
            $result = $this->visit($context->expresion());
        } else {
            $result = Result::buildVacio();
        }
        $result->isReturn = true;
        return $result;
	}
}