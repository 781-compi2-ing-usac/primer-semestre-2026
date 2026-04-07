<?php

namespace App\Env;

class Symbol {

    const CLASE_VARIABLE = "variable";
    const CLASE_CONSTANTE = "constante";
    const CLASE_FUNCION = "funcion";

    public $tipo;
    public $clase;
    public $params;
    public $fila;
    public $columna;
    public $tamaño_int = 4;
    public $tamaño_bool = 1;
    public $paramCount = 0;
    public $localVarCount = 0;
    public $totalSlots = 0;
    public $valor;

    public function __construct($tipo, $valor, $clase, $fila, $columna)
    {
        $this->tipo = $tipo;
        $this->valor = $valor;
        $this->clase = $clase;
        $this->fila = $fila;
        $this->columna = $columna;
        $this->params = null;
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
        $existe = array_key_exists($key, $this->values);
        if ($existe) {
            return $this->values[$key];
        }

        if (!$existe && $this->father !== null) {
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