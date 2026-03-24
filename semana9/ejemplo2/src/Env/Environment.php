<?php

namespace App\Env;

class Symbol {

    const CLASE_VARIABLE = "variable";
    const CLASE_CONSTANTE = "constante";
    const CLASE_FUNCION = "funcion";

    const SIZE_INT = 8;
    const SIZE_FLOAT = 8;
    const SIZE_BOOL = 1;
    const SIZE_STRING = 256;

    public $tipo;
    public $clase;
    public $valor;
    public $fila;
    public $columna;
    public $params;
    public $size;
    public $offset;

    public function __construct($tipo, $valor, $clase, $fila, $columna)
    {
        $this->tipo = $tipo;
        $this->valor = $valor;
        $this->clase = $clase;
        $this->fila = $fila;
        $this->columna = $columna;
        $this->params = null;
        $this->size = $this->calculateSize($tipo);
    }

    public static function calculateSize($tipo) {
        return match($tipo) {
            Result::INT, Result::FLOAT => self::SIZE_INT,
            Result::BOOL => self::SIZE_BOOL,
            Result::STRING => self::SIZE_STRING,
            default => 8,
        };
    }

    public static function asResult($symbol) : Result {
        return new Result($symbol->tipo, $symbol->valor);
    }
}

class Environment 
{
    private $father;
    private $values;
    private $offsetCounter;

    public function __construct($father = null) {
        if ($father !== null && !($father instanceof Environment)) {
            throw new \InvalidArgumentException("father must be an Environment or null");
        }
        $this->father = $father;
        $this->values = [];
        $this->offsetCounter = 0;
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

    public function exists($key): bool {
        if (array_key_exists($key, $this->values)) {
            return true;
        }
        if ($this->father !== null) {
            return $this->father->exists($key);
        }
        return false;
    }

    public function assign($key, $value) {    
        if (isset($this->values[$key])) {
            $this->values[$key] = $value;
            return;
        }
        if ($this->father !== null) {
            return $this->father->assign($key, $value);
        }
        throw new \Exception("Variable: ". $key ." no definida.");    
    }

    public function allocateStackSpace($size): int {
        $offset = $this->offsetCounter;
        $this->offsetCounter += $size;
        return $offset;
    }

    public function getOffsetCounter(): int {
        return $this->offsetCounter;
    }

    public function createChild(): Environment {
        return new Environment($this);
    }

    public function getFather(): ?Environment {
        return $this->father;
    }
}
