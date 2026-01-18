<?php
class Node {
    
    public function __construct($location) {
        $this->location = $location;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitNode($this);
    }

    public function __toString() {
        return "Soy una expresión genérica";
    }
}

class UnaryExpression extends Node {
    public $operator;
    public $operand;

    public function __construct($operator, $operand, $location) {
        parent::__construct($location);
        $this->operator = $operator;
        $this->operand = $operand;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitUnaryExpression($this);
    }

    public function __toString() {
        return "UnaryExpression(" . $this->operator . ", " . $this->operand . ")";
    }
}

class BinaryExpression extends Node {
    public $left;
    public $operator;
    public $right;

    public function __construct($left, $operator, $right, $location) {
        parent::__construct($location);
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitBinaryExpression($this);
    }

    public function __toString() {
        return "BinaryExpression(" . $this->left . " " . $this->operator . " " . $this->right . ")";
    }
}

class AgroupedExpression extends Node {
    public $expression;

    public function __construct($expression, $location) {
        parent::__construct($location);
        $this->expression = $expression;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitAgroupedExpression($this);
    }

    public function __toString() {
        return "AgroupedExpression(" . $this->expression . ")";
    }
}

class NumberExpression extends Node {
    public $value;

    public function __construct($value, $location) {
        parent::__construct($location);
        $this->value = $value;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitNumberExpression($this);
    }

    public function __toString() {
        return "NumberExpression(" . $this->value . ")";
    }
}

class BooleanExpression extends Node {
    public $value;
    public function __construct($value, $location) {
        parent::__construct($location);
        $this->value = $value;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitBooleanExpression($this);
    }
    public function __toString() {
        return "BooleanExpression(" . $this->value . ")";
    }
}

class StringExpression extends Node {
    public $value;
    public function __construct($value, $location) {
        parent::__construct($location);
        $this->value = $value;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitStringExpression($this);
    }
    public function __toString() {
        return "StringExpression(" . $this->value . ")";
    }
}

class PrintStatement extends Node {
    public $expression;

    public function __construct($expression, $location) {
        parent::__construct($location);
        $this->expression = $expression;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitPrintStatement($this);
    }

    public function __toString() {
        return "PrintStatement(" . $this->expression . ")";
    }
}

class VarDclStatement extends Node {
    public $id;
    public $expression;
    public function __construct($id, $expr, $location) {
        parent::__construct($location);
        $this->id = $id;
        $this->expression = $expr;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitVarDclStatement($this);
    }
    public function __toString() {
        return "VarDclStatement(" . $this->id . " " . $this->expression . ")";
    }
}

class VarAssignStatement extends Node {
    public $id;
    public $expr;
    public function __construct($id, $expr, $location) {
        parent::__construct($location);
        $this->id = $id;
        $this->expr = $expr;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitVarAssignStatement($this);
    }
    public function __toString() {
        return "VarAssignStatement(". $this->id . ", ". $this->expr .")";
    }
}

class RefVarStatement extends Node {
    public $id;
    public $dimensions;
    public function __construct($id, $dimensions=null, $location) {
        parent::__construct($location);
        $this->id = $id;
        $this->dimensions = $dimensions;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitRefVarStatement($this);
    }
    public function __toString() {
        return "RefVarStatement(" . $this->id . ")";
    }
}

class BlockStatement extends Node {
    public $stmts;
    public function __construct($stmts, $location) {
        parent::__construct($location);
        $this->stmts = $stmts;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitBlockStatement($this);
    }
    public function __toString() {
        return "BlockStatement(" . $this->stmts . ")";
    }
}

class IfStatement extends Node {
    public $cond;
    public $machedBlock;
    public $elseBlock;
    public function __construct($cond, $machedBlock, $elseBlock, $location) {
        parent::__construct($location);
        $this->cond = $cond;
        $this->machedBlock = $machedBlock;
        $this->elseBlock = $elseBlock;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitIfStatement($this);
    }

    public function __toString() {
        return "IfStatement(" . $this->cond . ", " . $this->machedBlock . ", " . $this->elseBlock .")";
    }
}

class WhileStatement extends Node {
    public $cond;
    public $block;
    public function __construct($cond, $block, $location) {
        parent::__construct($location);
        $this->cond = $cond;
        $this->block = $block;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitWhileStatement($this);
    }
    public function __toString() {
        return "WhileStatement(" . $this->cond . ", ". $this->block . ")";
    }
}

class FlowStatement extends Node {
    public $type;
    public $retval;
    public function __construct($type, $retval=null, $location) {
        parent::__construct($location);
        $this->type = $type;
        $this->retval = $retval;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitFlowStatement($this);
    }
    public function __toString() {
        return "FlowStatement(". $this->type. ", " . $this->retval . ")";
    }
}

class CallStatement extends Node {
    public $callee;
    public $args;
    public function __construct($callee, $args=null, $location) {
        parent::__construct($location);
        $this->callee = $callee;
        $this->args = $args;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitCallStatement($this);
    }
    public function __toString() {
        return "CallStatement(" .  $this->callee . ", ". $this->args .")";
    }
}

class FunctionDclStatement extends Node {
    public $id;
    public $params;
    public $block;
    public function __construct($id, $params=null, $block, $location) {
        parent::__construct($location);
        $this->id = $id;
        $this->params= $params;
        $this->block = $block;
    }   
    public function accept(Visitor $visitor) {
        return $visitor->visitFunctionDclStatement($this);
    }
    public function __toString() {
        return "FunctionDclStatement(". $this->id . ")";
    }
}

class ArrayExpression extends Node {
    public $values;    
    public $dimensions;
    public function __construct($values, $dimensions, $location) {
        parent::__construct($location);
        $this->values = $values;        
        $this->dimensions = $dimensions;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitArrayExpression($this);
    }
    public function __toString() {        
        return "ArrayExpression(" . count($this->dimensions) . ")";
    }
}