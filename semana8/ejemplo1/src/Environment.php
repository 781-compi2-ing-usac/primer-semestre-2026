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
        $exists = array_key_exists($key, $this->values);
        if ($exists) {
            return $this->values[$key];
        }

        if ($this->father !== null) {
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
        if (array_key_exists($key, $this->values)) {
            $this->values[$key] = $value;
            return;
        }
        if ($this->father !== null) {
            return $this->father->assign($key, $value);
        }
        throw new Exception("Variable: ". $key ." no definida.");    
    }
}