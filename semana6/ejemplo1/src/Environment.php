<?php

class Environment {
    private $father;
    private $values;

    public function __construct($father = null) {
        if ($father !== null && !($father instanceof Environment)) {
            throw new InvalidArgumentException("father must be an Environment or null");
        }
        $this->father = $father;
        $this->values = [];
    }

    public function set($key, $value) {
        $this->values[$key] = $value;
    }

    public function get($key) {
        $actual = $this->values[$key];        
        if ($actual !== null) {
            return $actual;
        }

        if ($actual === null && $this->father !== null) {
            return $this->father->get($key);
        }
        
        throw new Exception("Variable: '" . $key ."' no definida.");
    }

    public function &get_ref($key) {
        if (array_key_exists($key, $this->values)) {
            return $this->values[$key];
        }

        if ($this->father !== null) {
            return $this->father->get_ref($key);
        }

        throw new Exception("Variable: '" . $key ."' no definida.");
    }

    public function assign($key, $value) {    
        if ($this->values[$key] !== null) {
            $this->values[$key] = $value;
            return;
        }
        if ($this->father !== null) {
            return $this->father->assign($key, $value);
        }
        throw new Exception("Variable: ". $key ." no definida.");    
    }
}