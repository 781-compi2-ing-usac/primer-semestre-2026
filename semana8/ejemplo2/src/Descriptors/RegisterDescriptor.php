<?php

namespace App\Descriptors;

class RegisterDescriptor
{
    public $name;
    public $variables = [];
    public $isBusy = false;
    public $onStack = false;
    public $stackOffset = null;

    public function __construct($name) {
        $this->name = $name;
    }

    public function addVariable($varName) {
        if (!in_array($varName, $this->variables)) {
            $this->variables[] = $varName;
            $this->isBusy = true;
        }
    }

    public function removeVariable($varName) {
        $this->variables = array_filter($this->variables, function($v) use ($varName) {
            return $v !== $varName;
        });
        if (empty($this->variables)) {
            $this->isBusy = false;
        }
    }

    public function clear() {
        $this->variables = [];
        $this->isBusy = false;
        $this->onStack = false;
        $this->stackOffset = null;
    }

    public function getVariables() {
        return $this->variables;
    }

    public function __toString() {
        return $this->name;
    }
}
