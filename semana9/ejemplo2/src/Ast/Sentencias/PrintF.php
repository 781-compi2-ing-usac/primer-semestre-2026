<?php

namespace App\Ast\Sentencias;

use Context\PrintStatementContext;
use Context\ProgramContext;
use Context\BlockStatementContext;

trait PrintF
{
    public function visitPrintStatement(PrintStatementContext $ctx) {
        $result = $this->visit($ctx->expresion());
        
        $this->asmGenerador->comment("Print");
        $this->regs->pop();
        $this->asmGenerador->printInt();
    }

    public function visitProgram(ProgramContext $ctx) {
        foreach ($ctx->stmt() as $stmt) {
            $this->visit($stmt);
        }
        $this->asmGenerador->endProgram();
        return $this->asmGenerador;
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        foreach ($ctx->stmt() as $stmt) {
            $this->visit($stmt);
        }
    }
}
