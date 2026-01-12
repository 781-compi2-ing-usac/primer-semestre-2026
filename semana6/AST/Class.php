<?php

class ClassType extends Invocable {
    public $name;
    public $properties;
    public $methods;
    public function __construct($name, $properties, $methods) {
        $this->name = $name;
        $this->properties = $properties;
        $this->methods = $methods;
    }

    public function search_method($name) {
        if (array_key_exists($name, $this->methods)) {
            return $this->methods[ $name ];
        }
        throw new Exception("No existe ese método para esta clase");
    }

    public function get_arity() {
        $constructor = $this->search_method('constructor');

        if ($constructor !== null) {
            return $constructor->get_arity();
        }
        return 0;
    }

    public function invoke($interpreter, $args) {
        $newInstance = new Instance($this);

        foreach ($this->properties as $name => $value) {
            $newInstance->set( $name, $value->accept($interpreter));
        }

        $constructor = $this->search_method('constructor');
        if ($constructor !== null) {
            $constructor->link($newInstance)->invoke($interpreter, $args);
        }

        return $newInstance;
    }
}

class Instance {
    public $class;
    public $properties;
    public function __construct($class) {
        $this->class = $class;
        $this->properties = array();
    }
    public function set($name, $value) {
        $this->properties[ $name ] = $value;
    }
    public function get($name) {
        if(array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
        $method = $this->class->search_method($name);
        if ($method !== null) {
            return $method->link($this);
        }
        throw new Exception("Propiedad no encontrada");
    }
}