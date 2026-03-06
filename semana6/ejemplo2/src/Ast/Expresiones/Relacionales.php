<?php

namespace App\Ast\Expresiones;

use Context\RelacionalExpresionContext;
use App\Env\Result;

trait Relacionales 
{
    public static $tablaTiposIgualdad = array(
        Result::INT => [Result::INT => Result::BOOLEAN, Result::FLOAT => Result::BOOLEAN], //Result::FLOAT => Result::FLOAT
        Result::FLOAT => [Result::INT => Result::BOOLEAN, Result::STRING => Result::NULO]
    );

    public function visitRelacionalExpresion(RelacionalExpresionContext $ctx)
	{
	    $izquierda = $this->visit($ctx->expresion(0));
        $derecha = $this->visit($ctx->expresion(1));
        $op = $ctx->op->getText();

        //primera agrupación siendo aritmeticas
        switch ($op) {
            case '==':
                $tipo = self::$tablaTiposIgualdad[$izquierda->tipo][$derecha->tipo];
                if ($tipo && $tipo != Result::NULO) {
                    $valor = $izquierda->valor == $derecha->valor;
                    return new Result($tipo, $valor);
                }
                error_log("Operacion entre tipos no valida");
                return Result::buildVacio();
            case '-':
                return $izquierda - $derecha;
            case '*':
                return $izquierda * $derecha;
            case '/':
                return $izquierda / $derecha;
            default:
                throw new \Exception("Operador desconocido: " . $op);
        }
	}
    
}