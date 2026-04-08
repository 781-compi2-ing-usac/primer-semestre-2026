<?php

namespace App;

use Context\BlockStatementContext;
use Context\ProgramContext;
use Context\FunctionDeclarationContext;
use App\Stack\StackManager;
use App\Env\Environment;
use App\Ast\Sentencias\FuncionDeclaracion;
use App\Ast\Sentencias\Transferencia;
use App\Ast\Sentencias\Flujo\IfStatement;
use App\Ast\Sentencias\FunctionGenerator;
use App\Ast\Expresiones\FunctionCall;

class Compiler extends \GrammarBaseVisitor
{
    use \App\Ast\Expresiones\Aritmeticas;
    use \App\Ast\Expresiones\Booleanas;
    use \App\Ast\Expresiones\Primitivos;
    use \App\Ast\Expresiones\FunctionCall;
    use \App\Ast\Sentencias\PrintF;
    use \App\Ast\Sentencias\FuncionDeclaracion;
    use \App\Ast\Sentencias\Transferencia;
    use \App\Ast\Sentencias\Flujo\IfStatement;

    public $asmGenerador;
    public $stack;
    public $env;
    private $functionGenerator;
    private $mainStatements = [];
    private $functionSymbols = [];

    public function __construct() {
        $this->asmGenerador = new \App\ARM\ASMGenerator();
        $this->stack = new StackManager($this->asmGenerador);
        $this->env = new Environment();
        $this->functionGenerator = new FunctionGenerator($this->asmGenerador, $this->stack, $this->env, $this);
    }

    public function visitProgram(ProgramContext $ctx) {
        foreach ($ctx->stmt() as $stmt) {
            if ($stmt instanceof FunctionDeclarationContext) {
                $this->visit($stmt);
                $this->functionSymbols[] = $stmt;
            } else {
                $this->mainStatements[] = $stmt;
            }
        }

        foreach ($this->mainStatements as $stmt) {
            $this->visit($stmt);
        }

        foreach ($this->functionSymbols as $funcCtx) {
            $funcName = $funcCtx->ID()->getText();
            $symbol = $this->env->get($funcName);
            $this->functionGenerator->generateFunction($funcName, $symbol);
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
