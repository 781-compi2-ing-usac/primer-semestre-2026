<?php 

namespace App;

use Context\{ ProgramContext };
use Context\{ BlockStatementContext }; 

use App\Ast\{PrintF, Declaracion, Asignacion};
use App\Ast\Expresiones\{Aritmeticas, Primitivos};
use App\Env\Environment;

class Interpreter extends \GrammarBaseVisitor 
{
    use Aritmeticas, Primitivos, PrintF, Declaracion, Asignacion;
    private $console;
    private $env;

    public function __construct() {
        $this->console = "";
        $this->env = new Environment();
    }

    public function visitProgram(ProgramContext $ctx) {                  
        foreach ($ctx->stmt() as $stmt) {            
            $this->visit($stmt);
        }
        return $this->console;
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);
        foreach ($ctx->stmt() as $stmt) {            
            $this->visit($stmt);
        }
        $this->env = $prevEnv;
    }
}