<?php
class Interpreter implements Visitor {
    public $output = "";
    public $env;
    public function __construct() {
        $this->output = "\n";
        $this->env = new Environment();
        $this->embeded = require __DIR__ ."/Natives.php";
        foreach ($embeded as $key => $func) {
            $this->env->set($key, $func);
        }
    }

    public function visitNode(Node $node) {
        throw new Exception("Cannot interpret generic expression");
    }

    public function visitUnaryExpression(UnaryExpression $node) {
        $operand = $node->operand->accept($this);
        switch ($node->operator) {
            case '+':
                return +$operand;
            case '-':
                return -$operand;
            case '!':
                return (int) !$operand;
            default:
                throw new Exception("Unknown unary operator: " . $node->operator);
        }
    }

    public function visitBinaryExpression(BinaryExpression $node) {
        $left = $node->left->accept($this);
        $right = $node->right->accept($this);
        switch ($node->operator) {
            case '+':
                return $left + $right;
            case '-':
                return $left - $right;
            case '*':
                return $left * $right; 
            case '<':
                return (int) $left < $right;                           
            case '>':
                return (int) $left > $right;
            case '==':
                return (int) $left == $right;
            default:
                throw new Exception("Unknown binary operator: " . $node->operator);
        }
    }

    public function visitAgroupedExpression(AgroupedExpression $node) {
        return $node->expression->accept($this);
    }

    public function visitNumberExpression(NumberExpression $node) {
        return (int) $node->value;
    }

    public function visitBooleanExpression(BooleanExpression $node) {
        $value = $node->value;        
        return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN); 
    }

    public function visitStringExpression(StringExpression $node) {
        // Aquí se puede agregar lógica para manejar \n y ese tipo de caracteres.
        $result = str_replace("\"", "", $node->value);
        $result = str_replace("\\n", "\n", $result);
        return (string) $result;
    }

    public function visitPrintStatement(PrintStatement $node) {
        $value = $node->expression->accept($this);              
        $this->output .= $value . "\n";
    }

    public function visitVarDclStatement(VarDclStatement $node) {
        $value = $node->expression->accept($this);
        $key = $node->id;        
        $this->env->set($key, $value);        
    }

    public function visitVarAssignStatement(VarAssignStatement $node){
        $value = $node->expr->accept($this);
        $key = $node->id;
        $this->env->assign($key, $value);
    }

    public function visitRefVarStatement(RefVarStatement $node){        
        $key = $node->id;        
        return $this->env->get($key);
    }

    public function visitBlockStatement(BlockStatement $node){
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);
        foreach ($node->stmts as $stmt) {
            $retVal = $stmt->accept($this);
            if ($retVal instanceof FlowType) {                
                $this->env = $prevEnv;                
                return $retVal;
            }
        }
        $this->env = $prevEnv;
    }

    public function visitIfStatement(IfStatement $node){
        $condition = filter_var($node->cond->accept($this), FILTER_VALIDATE_BOOLEAN);
        if ($condition) {
            $retVal = $node->machedBlock->accept($this);
            if ($retVal instanceof FlowType) {
                return $retVal;
            }
        } 
        if (!$condition and $node->elseBlock !== null) {
            $retVal = $node->elseBlock->accept($this);
            if ($retVal instanceof FlowType) {
                return $retVal;
            }
        }
    }

    public function visitWhileStatement(WhileStatement $node){
        do {
            $condition = filter_var($node->cond->accept($this), FILTER_VALIDATE_BOOLEAN);
            if (!$condition) {                
                break;
            }
            $retVal = $node->block->accept($this);            
            if ($retVal instanceof BreakType) {                    
                break;
            } elseif ($retVal instanceof ReturnType) {
                return $retVal;
            }

        } while ($condition);
    }

    public function visitFlowStatement(FlowStatement $node){        
        if ($node->type === 1) {
            return new ContinueType();
        } elseif ($node->type === 2) {            
            return new BreakType();
        } elseif ($node->type === 3) {
            if ($node->retval !== null) {
                $value = $node->retval->accept($this);
                return new ReturnType($value);
            }
            return new ReturnType();
        }
        throw new Exception("Unkown flow statement");
    }

    public function visitCallStatement(CallStatement $node){
        $function = $node->callee->accept($this);
        $args = array();
        if ($node->args !== null) {            
            foreach ($node->args as $arg) {
                $args[] = $arg->accept($this);
            }
        }
        if (!($function instanceof Invocable)) {            
            throw new Exception("No es invocable.");
        }
        if ($function->get_arity() !== count($args)) {
            echo $function->get_arity();
            throw new Exception("Numero incorrecto de argumentos.");
        }
        return $function->invoke($this, $args);
    }

    public function visitFunctionDclStatement(FunctionDclStatement $node){
        $func = new Foreign($node, $this->env);
        $this->env->set($node->id, $func);
    }    

    public function visitArrayExpression(ArrayExpression $node){                    
        $level = 0;
        $defaut = array();
        # Construcción de Array new var[num][num]...[...]
        if ($node->values === null) {
            foreach ($node->dimensions as $dimension) {
                $length = $dimension->accept($this);                
                if (!is_int($length)) {
                    throw new Exception("Coloque un valor válido en array");
                }
                if ($level == 0) {
                    $defaut = array_fill(0, $length,null);                        
                } else {
                    $newDimension = array_fill(0, $length, $defaut);
                    $defaut = $newDimension;
                }
                $level++;
            }        
            $node->values = $defaut;
            return $node;        
        }
        # Construcción de Array {{1,2,3,4},{5,6,7,8},...{...}}                
        $length = count($node->values);
        $node->dimensions[] = $length;
        $accepted_vals = array();
        foreach ($node->values as $dimension) {
            $result = $dimension->accept($this);
            $accepted_vals[] = $result;
            if (!($result instanceof ArrayExpression)) {                                
                return $node;
            } else {
                $node->dimensions = array_merge($node->dimensions, $result->dimensions);
                return $node;
            }
        }                    
    }
}