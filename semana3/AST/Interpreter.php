<?php
class Interpreter implements Visitor {
    public $output = "";
    private $env;
    public function __construct() {
        $this->output = "\n";
        $this->env = new Environment();
    }

    public function visitExpression(Expression $expr) {
        throw new Exception("Cannot interpret generic expression");
    }

    public function visitUnaryExpression(UnaryExpression $expr) {
        $operand = $expr->operand->accept($this);
        switch ($expr->operator) {
            case '+':
                return +$operand;
            case '-':
                return -$operand;
            default:
                throw new Exception("Unknown unary operator: " . $expr->operator);
        }
    }

    public function visitBinaryExpression(BinaryExpression $expr) {
        $left = $expr->left->accept($this);
        $right = $expr->right->accept($this);
        switch ($expr->operator) {
            case '+':
                return $left + $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right;                            
            default:
                throw new Exception("Unknown binary operator: " . $expr->operator);
        }
    }

    public function visitAgroupedExpression(AgroupedExpression $expr) {
        return $expr->expression->accept($this);
    }

    public function visitNumberExpression(NumberExpression $expr) {
        return (int) $expr->value;
    }

    public function visitPrintStatement(PrintStatement $expr) {
        $value = $expr->expression->accept($this);
        $this->output .= $value . "\n";
    }

    public function visitVarDclStatement(VarDclStatement $expr) {
        $value = $expr->expression->accept($this);
        $key = $expr->id;
        $this->env->set($key, $value);
    }

    public function visitRefVarStatement(RefVarStatement $expr){        
        $key = $expr->id;
        return $this->env->get($key);
    }

    public function visitBlockStatement(BlockStatement $expr){
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);
        foreach ($expr->stmts as $stmt) {
            $stmt->accept($this);
        }
        $this->env = $prevEnv;
    }
}