<?php

namespace App\Ast\Sentencias\Flujo;

use Context\IfStatementContext;
use App\Env\Result;

trait IfStatement 
{
    public function visitIfStatement(IfStatementContext $context)
	{
	    $condition = $this->visit($context->expresion()); 
        $flow = Result::buildVacio();
        if ($condition->tipo != Result::BOOLEAN) {
            error_log("La expresion no es un boolean");
            return $flow;
        }
              
        if ($condition->valor) {            
            $flow = $this->visit($context->block());
        } else if ($context->else() !== null) {
            $flow = $this->visit($context->else());
        }
        return $flow;
	}
}