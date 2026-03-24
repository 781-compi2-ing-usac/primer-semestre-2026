<?php

namespace App;

use Context\BlockStatementContext;
use Context\ProgramContext; 
use App\Descriptors\RegisterManager;

class Compiler extends \GrammarBaseVisitor
{
    use \App\Ast\Expresiones\Aritmeticas;
    use \App\Ast\Expresiones\Booleanas;
    use \App\Ast\Expresiones\Primitivos;
    use \App\Ast\Sentencias\PrintF;

    public $asmGenerador;
    public $regs;

    public function __construct() {
        $this->asmGenerador = new \App\ARM\ASMGenerator();
        $this->regs = new RegisterManager($this->asmGenerador);
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
