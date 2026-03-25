<?php

namespace App\Ast\Sentencias;

use Context\ReturnStatementContext;
use App\Env\Result;

trait Transferencia
{
    public $returnLabels = [];

    public function visitReturnStatement(ReturnStatementContext $ctx)
    {
        $result = Result::buildVacio();
        $result->isReturn = true;
        
        if ($ctx->expresion() !== null) {
            $returnValue = $this->visit($ctx->expresion());
            $returnReg = $this->stack->popValue();
            
            $this->asmGenerador->comment("Return: guardar valor de retorno");
            $this->asmGenerador->mov("x0", $returnReg);
        }
        
        if (isset($this->returnLabels[$this->currentFunction])) {
            $returnLabel = $this->returnLabels[$this->currentFunction];
            $this->asmGenerador->b($returnLabel);
        }
        
        return $result;
    }

    public function setCurrentFunction($name) {
        $this->currentFunction = $name;
    }
}
