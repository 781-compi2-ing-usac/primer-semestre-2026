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
            case '!':
                return (int) !$operand;
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
            case '<':
                return (int) $left < $right;                           
            case '>':
                return (int) $left > $right;
            case '==':
                return (int) $left == $right;
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

    public function visitBooleanExpression(BooleanExpression $expr) {
        $value = $expr->value;        
        return (int) filter_var($value, FILTER_VALIDATE_BOOLEAN); 
    }

    public function visitStringExpression(StringExpression $expr) {
        // Aquí se puede agregar lógica para manejar \n y ese tipo de caracteres.
        $result = str_replace("\"", "", $expr->value);
        $result = str_replace("\\n", "\n", $result);
        return (string) $result;
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

    public function visitVarAssignStatement(VarAssignStatement $expr){
        $value = $expr->expr->accept($this);
        $key = $expr->id;
        $this->env->assign($key, $value);
    }

    public function visitRefVarStatement(RefVarStatement $expr){        
        $key = $expr->id;
        return $this->env->get($key);
    }

    public function visitBlockStatement(BlockStatement $expr){
        $prevEnv = $this->env;
        $this->env = new Environment($prevEnv);
        foreach ($expr->stmts as $stmt) {
            $retVal = $stmt->accept($this);
            if ($retVal instanceof FlowType) {                
                $this->env = $prevEnv;                
                return $retVal;
            }
        }
        $this->env = $prevEnv;
    }

    public function visitIfStatement(IfStatement $expr){
        $condition = filter_var($expr->cond->accept($this), FILTER_VALIDATE_BOOLEAN);
        if ($condition) {
            $retVal = $expr->machedBlock->accept($this);
            if ($retVal instanceof FlowType) {
                return $retVal;
            }
        } 
        if (!$condition and $expr->elseBlock !== null) {
            $retVal = $expr->elseBlock->accept($this);
            if ($retVal instanceof FlowType) {
                return $retVal;
            }
        }
    }

    public function visitWhileStatement(WhileStatement $expr){
        do {
            $condition = filter_var($expr->cond->accept($this), FILTER_VALIDATE_BOOLEAN);
            if (!$condition) {                
                break;
            }
            $retVal = $expr->block->accept($this);            
            if ($retVal instanceof BreakType) {    
                echo "Haciendo break";
                break;
            }
        } while ($condition);
    }

    public function visitFlowStatement(FlowStatement $expr){        
        if ($expr->type === 1) {
            return new ContinueType();
        } elseif ($expr->type === 2) {            
            return new BreakType();
        } elseif ($expr->type === 3) {
            if ($expr->retval !== null) {
                $value = $expr->retval->accept($this);
                return new ReturnType($value);
            }
            return new ReturnType();
        }
        throw new Exception("Unkown flow statement");
    }

    public function visitCallStatement(CallStatement $expr){
        $function = $expr->callee->accept($this);
        $args = array();
        if ($expr->args !== null) {            
            foreach ($expr->args as $arg) {
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

    public function visitFunctionDclStatement(FunctionDclStatement $expr){
        $func = new Foreign($expr, $this->env);
        $this->env->set($expr->id, $func);
    }

    public function visitClassDclStatement(ClassDclStatement $expr){
        $functions = array();
        $properties = array();
        
        foreach ($expr->block->stmts as $stmt) {
            if ($stmt instanceof FunctionDclStatement) {
                $functions[] = new FunctionDclStatement($stmt, $this->env);
            } elseif ($stmt instanceof VarDclStatement) {
                $properties[$stmt->id] = $stmt->expression;
            }
        }

        $newClass = new ClassType($expr->id, $properties, $functions);
        $this->env->set($expr->id, $newClass);
    }

    public function visitInstanceExpression(InstanceExpression $expr){
        $class = $this->env->get($expr->id);
        $args = array();
        foreach ($expr->args as $arg) {
                $args[] = $arg->accept($this);
        }

        if (!($class instanceof ClassType)) {
                throw new Exception("No es posible instanciar algo que no es una clase");
        }
        return $class->invoke($this, $args);
    }
}