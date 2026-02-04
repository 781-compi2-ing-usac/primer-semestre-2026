<?php 

use Context\BinaryExpressionContext;
use Context\UnaryExpressionContext;
use Context\PrimaryExpressionContext;
use Context\GroupedExpressionContext;
use Context\PrintStatementContext;
use Context\ProgramContext;

class Interpreter extends GrammarBaseVisitor {
    public $console;

    public function visitUnaryExpression(UnaryExpressionContext $ctx) {        
        return - $this->visit($ctx->e());
    }

    public function visitBinaryExpression(BinaryExpressionContext $ctx) {
        $left = $this->visit($ctx->e(0));
        $right = $this->visit($ctx->e(1));
        $op = $ctx->op->getText();                
        switch($op) {
            case '+':
                return $left + $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right;
            case '/':
                return $left / $right;
            default:
                throw new Exception("Unknown operator: " . $op);
        }
    }

    public function visitPrimaryExpression(PrimaryExpressionContext $ctx) {
        return intval($ctx->INT()->getText());
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->e());
    }

    public function visitPrintStatement(PrintStatementContext $ctx) {
        $value = $this->visit($ctx->e());        
        $this->console .= $value . "\n";
        return $value;
    }

    public function visitProgram(ProgramContext $ctx) {          
        foreach ($ctx->stmt() as $stmt) {
            $this->visit($stmt);
        }
        return $this->console;
    }
}