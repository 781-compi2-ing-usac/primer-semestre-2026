<?php 

namespace App;

use Context\{ ProgramContext };
use Context\{ BlockStatementContext }; 

use App\Ast\{PrintF, Declaracion, Asignacion};
use App\Ast\Expresiones\{Aritmeticas, Primitivos, Relacionales, FunctionCall};
use App\Ast\Sentencias\Flujo\{IfStatement};
use App\Ast\Sentencias\{Transferencia, FuncionDeclaracion};
use App\Env\Environment;
use App\Env\Result;

class Interpreter extends \GrammarBaseVisitor 
{
    use Aritmeticas, Primitivos, PrintF, Declaracion, Asignacion, IfStatement, Relacionales,
    Transferencia, FuncionDeclaracion, FunctionCall;
    private $console;
    private $env;
    private $envGlobal;

    public function __construct() {
        $this->console = "";
        $this->env = new Environment();
        $this->envGlobal = $this->env;
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
            $result = $this->visit($stmt);
            if ($result->isReturn) {
                $this->env = $prevEnv;
                return $result;
            }
        }
        $this->env = $prevEnv;
        return Result::buildVacio();
    }
}