<?php
class Expression {
    
    public function __construct($location) {
        $this->location = $location;
    }

    public function accept(Visitor $visitor) {
        return $visitor->visitExpression($this);
    }

    public function __toString() {
        return "Soy una expresión genérica";
    }
}

class UnaryExpression extends Expression {
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

class BinaryExpression extends Expression {
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

class AgroupedExpression extends Expression {
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

class NumberExpression extends Expression {
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

class BooleanExpression extends Expression {
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

class StringExpression extends Expression {
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

class PrintStatement extends Expression {
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

class VarDclStatement extends Expression {
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

class VarAssignStatement extends Expression {
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

class RefVarStatement extends Expression {
    public $id;
    public function __construct($id, $location) {
        parent::__construct($location);
        $this->id = $id;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitRefVarStatement($this);
    }
    public function __toString() {
        return "RefVarStatement(" . $this->id . ")";
    }
}

class BlockStatement extends Expression {
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

class IfStatement extends Expression {
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

class WhileStatement extends Expression {
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

class FlowStatement extends Expression {
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

class CallStatement extends Expression {
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

class FunctionDclStatement extends Expression {
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

class ClassDclStatement extends Expression {
    public $id;
    public $block;
    public function __construct($id, $location) {
        parent::__construct($location);
        $this->id = $id;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitClassDclStatement($this);
    }
    public function __toString() {
        return "ClassDclStatement(" . $this->id . ")";
    }
}

class InstanceExpression extends Expression {
    public $id;
    public $args;
    public function __construct($id, $args, $location) {
        parent::__construct($location);
        $this->id = $id;
        $this->args= $args;
    }
    public function accept(Visitor $visitor) {
        return $visitor->visitInstanceExpression($this);
    }
    public function __toString() {
        return "InstanceExpression(" . $this->id . ")";
    }
}