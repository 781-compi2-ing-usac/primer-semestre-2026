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
        if ($value === null) {
            $value = "nil";
        }
        $this->output .= $value . "\n";
    }

    public function visitVarDclStatement(VarDclStatement $node) {        
        
        if ($node->expression !== null) {
            $value = $node->expression->accept($this);
        } else {
            $value = "null";
        }
        $key = $node->id;                
        $this->env->set($key, $value);        
    }

    public function visitVarAssignStatement(VarAssignStatement $node){
        $value = $node->expr->accept($this);
        if ($node->refVar instanceof ArrayAccessExp) {            
            $this->assignArray($node->refVar, $value);            
            return;
        }
        $key = $node->refVar->id;
        $this->env->assign($key, $value);
    }

    private function assignArray(ArrayAccessExp $node, $value) {
        $indexes = [];
        while ($node->base !== null){
            $index = $node->index->accept($this);
            array_unshift($indexes, $index);
            if ($node->base instanceof ArrayAccessExp) {
                $node = $node->base;
            } elseif ($node->base instanceof RefVarStatement) {
                $arrayId = $node->base->id;
                $array = $this->env->get($arrayId);
                // Whe get the reference to the array and navigate to the correct position
                $ref = &$array;
                foreach ($indexes as $idx) {
                    // Navigate to the next level
                    $ref = &$ref[$idx];
                }
                // Finally assign the value to the referenced position
                $ref = $value;
                // Update the array in the environment
                $this->env->assign($arrayId, $array);
                break;
            } else {
                throw new Exception("Invalid array access base.");
            }
        }
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

    public function visitArrayInitDcl(ArrayInitDcl $node) {
        $elements = array();
        foreach ($node->elements as $element) {
            $elements[] = $element->accept($this);
        }
        return $elements;
    }

    public function visitArrayNewDcl(ArrayNewDcl $node) {
        $dims = [];

        foreach ($node->dimensions as $dimExpr) {
            $value = $dimExpr->accept($this);

            if (!is_int($value) || $value < 0) {
                throw new Exception("Dimensión inválida de array");
            }

            $dims[] = $value;
        }
        
        return $this->allocate($dims, 0);
    }

    private function allocate($dims, $level) {
        $size = $dims[$level];

        if ($level === count($dims) - 1) {
            return array_fill(0, $size, null);
        }

        $arr = [];
        for ($i = 0; $i < $size; $i++) {
            $arr[] = $this->allocate($dims, $level + 1);
        }

        return $arr;
    }

    public function visitArrayAccessExp(ArrayAccessExp $node) {
        $container = $node->base->accept($this);
        $index = $node->index->accept($this);

        if (!is_array($container)) {
            throw new Exception("Intento de indexar un valor que no es un array");
        }

        if (!is_int($index)) {
            throw new Exception("Índice de array no entero");
        }

        if (!array_key_exists($index, $container)) {
            throw new Exception("Índice fuera de rango");
        }

        return $container[$index];
    }
}