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