<?php 

namespace App;

use Context\{ ProgramContext };
use Context\{ AssignmentStatementContext, BlockStatementContext }; 
use Context\{ GroupedExpressionContext, ReferenceExpressionContext };

use App\Ast\{PrintF, Declaracion};
use App\Ast\Expresiones\{Aritmeticas, Primitivos};
use App\Env\Environment;

class Interpreter extends \GrammarBaseVisitor 
{
    use Aritmeticas, Primitivos, PrintF, Declaracion;
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

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->expresion());
        $this->env->assign($varName, $value);
        return $value;
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);
        foreach ($ctx->stmt() as $stmt) {            
            $this->visit($stmt);
        }
        $this->env = $prevEnv;        
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->expresion());
    }

    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        return $this->env->get($varName);
    }
}