<?php 

use Context\ProgramContext;
use Context\PrintStatementContext;
use Context\VarDeclarationContext;
use Context\AssignmentStatementContext;
use Context\IfStatementContext;
use Context\WhileStatementContext;
use Context\ContinueStatementContext;
use Context\BreakStatementContext;
use Context\ReturnStatementContext;
use Context\FunctionDeclarationContext;
use Context\FunctionCallStatementContext;
use Context\ArrayAssignmentStatementContext;
use Context\BlockStatementContext;
use Context\EqualityExpressionContext;
use Context\InequalityExpressionContext;
use Context\AddExpressionContext;
use Context\ProductExpressionContext;
use Context\PrimaryExpressionContext;
use Context\UnaryExpressionContext;
use Context\GroupedExpressionContext;
use Context\IntExpressionContext;
use Context\ReferenceExpressionContext;
use Context\BoolExpressionContext;
use Context\FunctionCallExpressionContext;
use Context\ArrayExpressionContext;
use Context\ArrayAccessExpressionContext;
use Context\ParameterListContext;
use Context\ArgumentListContext;



class Compiler extends GrammarBaseVisitor {
    public $code;     
    public $r;

    public function __construct() {
        $this->code = new ASMGenerator();                
        $this->r = include __DIR__ . "/ARM/Constants.php";
    }

    public function visitProgram(ProgramContext $ctx) {                  
        foreach ($ctx->stmt() as $stmt) {            
            $this->visit($stmt);
        }
        $this->code->endProgram();
        return $this->code;
    }

    public function visitPrintStatement(PrintStatementContext $ctx) {        
        $this->visit($ctx->e());
        $this->code->comment("Imprimiendo el resultado de la expresión");
        $this->code->comment("Cargando el valor a imprimir en A0");
        $this->code->pop($this->r["A0"]);        
        $this->code->printInt($this->r["A0"]);
    }

    public function visitAddExpression(AddExpressionContext $ctx) {            
        if ($ctx->add() !== null) {
            $this->visit($ctx->add());
            $this->visit($ctx->prod());
            $op = $ctx->op->getText();

            $this->code->comment("Visitando expresión de suma/resta: " . $op);
            $this->code->comment("Evaluando el primer operando");
            $this->code->pop($this->r["T0"]);
            $this->code->comment("Evaluando el segundo operando");
            $this->code->pop($this->r["T1"]);

            switch ($op) {
                case '+':
                    $this->code->comment("Sumando T0 con T1");
                    $this->code->add($this->r["T0"], $this->r["T0"], $this->r["T1"]);
                    $this->code->push($this->r["T0"]);
                    break;
                case '-':
                    $this->code->comment("Restando T0 con T1");
                    $this->code->sub($this->r["T0"], $this->r["T0"], $this->r["T1"]);
                    $this->code->push($this->r["T0"]);
                    break;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            $this->visit($ctx->prod());
        }
    }

    public function visitProductExpression(ProductExpressionContext $ctx) {                
        if ($ctx->prod() !== null) {
            $this->visit($ctx->prod());
            $this->visit($ctx->unary());
            $op = $ctx->op->getText();

            $this->code->comment("Visitando expresión de producto: " . $op);
            $this->code->comment("Evaluando el primer operando");
            $this->code->pop($this->r["T0"]);
            $this->code->comment("Evaluando el segundo operando");
            $this->code->pop($this->r["T1"]);

            switch ($op) {
                case '*':
                    $this->code->comment("Multiplicando T0 con T1");  
                    $this->code->mul($this->r["T0"], $this->r["T0"], $this->r["T1"]);
                    $this->code->push($this->r["T0"]);                  
                    break;                 
                case '/':
                    $this->code->comment("Dividiendo T0 con T1");
                    $this->code->div($this->r["T0"], $this->r["T0"], $this->r["T1"]);
                    $this->code->push($this->r["T0"]);
                    break;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            $this->visit($ctx->unary());
        }   
    }

    public function visitPrimaryExpression(PrimaryExpressionContext $ctx) {
        return $this->visit($ctx->primary());
    }

    public function visitUnaryExpression(UnaryExpressionContext $ctx) {       
        $this->code->comment("Visitando expresión unaria"); 
        $this->visit($ctx->unary());    
        $this->code->comment("Cargando el valor en T0");    
        $this->code->pop($this->r["T0"]);
        $this->code->comment("Negando el valor en T0");
        $this->code->sub($this->r["T0"], $this->r["ZERO"], $this->r["T0"]);
        $this->code->push($this->r["T0"]);
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->e());
    }

    public function visitIntExpression(IntExpressionContext $ctx) {
        $this->code->comment("Cargando entero: " . $ctx->INT()->getText());
        $number = intval($ctx->INT()->getText());
        $this->code->li($this->r["T0"], $number);
        $this->code->push($this->r["T0"]);
    }      
}