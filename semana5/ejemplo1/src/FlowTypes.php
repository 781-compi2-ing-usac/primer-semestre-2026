<?php
class FlowType {
    public function __tostring() {
        return "FlowType";
    }
}

class ContinueType extends FlowType{
    public function __tostring() {
        return "ContinueType";
    }
}

class BreakType extends FlowType{
    public function __tostring() {
        return "BreakType";
    }
}

class ReturnType extends FlowType{
    public $value;
    public function __construct($value) {
        $this->value = $value;
    }
    public function __tostring() {
        return "ReturnType(" . $this->value . ")";
    }
}