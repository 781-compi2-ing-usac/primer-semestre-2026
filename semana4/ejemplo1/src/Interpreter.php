<?php 

use Context\ProgramContext;
use Context\PrintStatementContext;
use Context\VarDeclarationContext;
use Context\AssignmentStatementContext;
use Context\IfStatementContext;
use Context\WhileStatementContext;
use Context\ContinueStatementContext;
use Context\BreakStatementContext;
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



class Interpreter extends GrammarBaseVisitor {
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

    public function visitPrintStatement(PrintStatementContext $ctx) {
        $value = $this->visit($ctx->e());   
        if (is_bool($value)) {
            $value = $value ? "true" : "false";
        }
        $this->console .= $value . "\n";
        return $value;
    }

    public function visitVarDeclaration(VarDeclarationContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->set($varName, $value);
        return $value;
    }

    public function visitAssignmentStatement(AssignmentStatementContext $ctx) {
        $varName = $ctx->ID()->getText();
        $value = $this->visit($ctx->e());
        $this->env->assign($varName, $value);
        return $value;
    }

    public function visitIfStatement(IfStatementContext $ctx) {
        $condition = $this->visit($ctx->e());        
        if ($condition) {            
            $flow = $this->visit($ctx->block());
            if ($flow instanceof FlowType) {
                return $flow;
            }
        } else if ($ctx->else() !== null) {
            $flow = $this->visit($ctx->else());
            if ($flow instanceof FlowType) {
                return $flow;
            }
        }
    }

    public function visitWhileStatement(WhileStatementContext $ctx) {        
        do {
            $condition = $this->visit($ctx->e());
            if ($condition) {                
                $flow = $this->visit($ctx->block());
                if ($flow instanceof BreakType) {
                    break;
                }
            }
        } while ($condition);
    }

    public function visitContinueStatement(ContinueStatementContext $ctx) {        
        return new ContinueType();
    }

    public function visitBreakStatement(BreakStatementContext $ctx) {
        return new BreakType();
    }

    public function visitBlockStatement(BlockStatementContext $ctx) {
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);                
        foreach ($ctx->stmt() as $stmt) {            
            $flow = $this->visit($stmt);            
            if ($flow instanceof FlowType) {
                $this->env = $prevEnv;                
                return $flow;
            }
        }
        $this->env = $prevEnv;        
    }

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        if ($ctx->right !== null) {
            $left = $this->visit($ctx->left);
            $right = $this->visit($ctx->right);
            return $left == $right;
        } else {
            return $this->visit($ctx->left);
        }
    }

    public function visitInequalityExpression(InequalityExpressionContext $ctx) {
        if ($ctx->right !== null) {
            $left = $this->visit($ctx->left);
            $right = $this->visit($ctx->right);
            $op = $ctx->op->getText();

            switch ($op) {
                case '>':
                    return $left > $right;
                case '<':
                    return $left < $right;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            return $this->visit($ctx->left);
        }
    }

    public function visitAddExpression(AddExpressionContext $ctx) {
        if ($ctx->add() !== null) {
            $add = $this->visit($ctx->add());
            $prod = $this->visit($ctx->prod());
            $op = $ctx->op->getText();

            switch ($op) {
                case '+':
                    return $add + $prod;
                case '-':
                    return $add - $prod;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            return $this->visit($ctx->prod());
        }
    }

    public function visitProductExpression(ProductExpressionContext $ctx) {
        if ($ctx->prod() !== null) {
            $prod = $this->visit($ctx->prod());
            $unary = $this->visit($ctx->unary());
            $op = $ctx->op->getText();

            switch ($op) {
                case '*':
                    return $prod * $unary;
                case '/':
                    return $prod / $unary;
                default:
                    throw new Exception("Operador desconocido: " . $op);
            }
        } else {
            return $this->visit($ctx->unary());
        }
    }

    public function visitPrimaryExpression(PrimaryExpressionContext $ctx) {
        return $this->visit($ctx->primary());
    }

    public function visitUnaryExpression(UnaryExpressionContext $ctx) {        
        return - $this->visit($ctx->unary());
    }

    public function visitGroupedExpression(GroupedExpressionContext $ctx) {
        return $this->visit($ctx->e());
    }

    public function visitIntExpression(IntExpressionContext $ctx) {
        return intval($ctx->INT()->getText());
    }  
    
    public function visitReferenceExpression(ReferenceExpressionContext $ctx) {
        $varName = $ctx->ID()->getText();
        return $this->env->get($varName);
    }

    public function visitBoolExpression(BoolExpressionContext $ctx) {
        return $ctx->bool->getText() === 'true';
    }
}