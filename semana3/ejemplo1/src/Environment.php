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
}