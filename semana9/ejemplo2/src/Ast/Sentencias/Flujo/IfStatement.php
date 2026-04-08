<?php

namespace App\Ast\Sentencias\Flujo;

use Context\IfStatementContext;
use Context\ElseContext;
use App\Env\Result;

trait IfStatement
{
    public function visitIfStatement(IfStatementContext $ctx)
    {
        $conditionResult = $this->visit($ctx->expresion());
        
        $conditionReg = $this->stack->popValue();
        
        $endLabel = $this->asmGenerador->generateLabel("if_end");
        $elseLabel = $ctx->else() !== null ? $this->asmGenerador->generateLabel("if_else") : $endLabel;
        
        $this->asmGenerador->comment("If: evaluar condición");
        $this->asmGenerador->cmp($conditionReg, "xzr");
        
        if ($ctx->else() !== null) {
            $this->asmGenerador->beq($elseLabel);
        } else {
            $this->asmGenerador->beq($endLabel);
        }
        
        $this->asmGenerador->comment("If: ejecutar bloque true");
        $this->visit($ctx->block());
        
        if ($ctx->else() !== null) {
            $this->asmGenerador->b($endLabel);
            $this->asmGenerador->label($elseLabel);
            $this->asmGenerador->comment("If: ejecutar bloque else");
            $this->visit($ctx->else());
        }
        
        $this->asmGenerador->label($endLabel);
        
        return Result::buildVacio();
    }
}
