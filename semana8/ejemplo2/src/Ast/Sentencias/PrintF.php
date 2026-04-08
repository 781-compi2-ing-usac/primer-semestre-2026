<?php

namespace App\Ast\Sentencias;

use Context\PrintStatementContext;
use Context\ProgramContext;
use Context\BlockStatementContext;

trait PrintF
{
    public function visitPrintStatement(PrintStatementContext $ctx) {
        $result = $this->visit($ctx->expresion());
        
        $this->asmGenerador->comment("Imprimiendo valor");
        
        $regName = $this->regs->getValueFromResult($result);
        
        $argReg = $this->regs->getArgumentRegisters(1)[0];
        $this->asmGenerador->mov($argReg->name, $regName);
        
        $this->asmGenerador->printInt($argReg->name);
        
        $this->regs->freeRegister($result);
    }

    public function visitProgram(ProgramContext $ctx) {
        $fp = $this->regs->getFramePointer();
        $sp = $this->regs->getStackPointer();
        
        $this->asmGenerador->comment("Inicializar FP y ajustar stack");
        $this->asmGenerador->mov($fp->name, $sp->name);
        
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
