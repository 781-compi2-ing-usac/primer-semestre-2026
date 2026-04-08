<?php

namespace App\Env;

class Result
{
    const INT = "Int";
    const FLOAT = "Float";
    const BOOL = "Bool";
    const STRING = "String";
    const NULO = "null";

    public $tipo;
    public $valor;
    public $onStack;
    public $offset;

    public function __construct($tipo, $valor = null, $onStack = false, $offset = 0)
    {
        $this->tipo = $tipo;
        $this->valor = $valor;
        $this->onStack = $onStack;
        $this->offset = $offset;
    }

    public static function temp($tipo, $valor) {
        return new Result($tipo, $valor, false, 0);
    }

    public static function stack($tipo, $offset) {
        return new Result($tipo, null, true, $offset);
    }

    public static function buildVacio() {
        return new Result(self::NULO, null);
    }

    public function getRegisterName() {
        return $this->valor;
    }

    public function getOffset() {
        return $this->offset;
    }

    public function isOnStack() {
        return $this->onStack;
    }
}
