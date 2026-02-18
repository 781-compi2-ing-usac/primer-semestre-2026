<?php

namespace App\Env;

use App\Env\Symbol;

class Result
{
    const BOOLEAN = "Bool";
    const CHAR = "Character";
    const INT = "Int";
    const FLOAT = "Float";
    const STRING = "String";
    const NULO = "null";

    public $tipo;
    public $valor;

    public function __construct($tipo, $valor)
    {
        $this->tipo = $tipo;
        $this->valor = $valor;
    }

    public static function buildVacio(): Result {
        return new Result(self::NULO, null);
    }
}
