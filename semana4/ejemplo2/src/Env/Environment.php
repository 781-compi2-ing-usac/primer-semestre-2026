<?php

namespace App\Env;

class Symbol {

    const CLASE_VARIABLE = "variable";
    const CLASE_CONSTANTE = "constante";
    const CLASE_FUNCION = "funcion";

    public $tipo;
    public $valor; //para el segundo proyecto se quita
    public $clase;
    public $fila;
    public $columna;

    public function __construct($tipo, $valor, $clase, $fila, $columna)
    {
        $this->tipo = $tipo;
        $this->valor = $valor;
        $this->clase = $clase;
        $this->fila = $fila;
        $this->columna = $columna;
    }

    public static function asResult($symbol) : Result {
        return new Result($symbol->tipo, $symbol->valor);
    }
}

class Environment 
{
    private $father;
    private $values;

    public function __construct($father = null) {
        if ($father !== null && !($father instanceof Environment)) {
            throw new \InvalidArgumentException("father must be an Environment or null");
        }
        $this->father = $father;
        $this->values = [];
    }

    public function set($key, $value) {
        $this->values[$key] = $value;
    }

    public function get($key): Symbol {
        $actual = $this->values[$key];        
        if ($actual !== null) {
            return $actual;
        }

        if ($actual === null && $this->father !== null) {
            return $this->father->get($key);
        }
        
        throw new \Exception("Variable: '" . $key ."' no definida.");
    }

    //sin usar
    public function assign($key, $value) {    
        if ($this->values[$key] !== null) {
            $this->values[$key] = $value;
            return;
        }
        if ($this->father !== null) {
            return $this->father->assign($key, $value);
        }
        throw new \Exception("Variable: ". $key ." no definida.");    
    }
}