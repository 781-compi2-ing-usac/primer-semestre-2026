<?php


namespace App\Ast\Expresiones;

use Context\AritmeticaExpressionContext;
use Context\NegacionExpressionContext;
use App\Env\Result;

trait Aritmeticas
{
    public static $tablaTiposSuma = array(
        Result::INT => [Result::INT => Result::INT, Result::FLOAT => Result::FLOAT], //Result::FLOAT => Result::FLOAT
        Result::FLOAT => [Result::INT => Result::FLOAT, Result::STRING => Result::NULO]
    );

    public function visitAritmeticaExpression(AritmeticaExpressionContext $ctx) {
        $izquierda = $this->visit($ctx->expresion(0));
        $derecha = $this->visit($ctx->expresion(1));
        $op = $ctx->op->getText();

        //primera agrupación siendo aritmeticas
        switch ($op) {
            case '+':
                $tipo = self::$tablaTiposSuma[$izquierda->tipo][$derecha->tipo];
                if ($tipo && $tipo != Result::NULO) {
                    $valor = $izquierda->valor + $derecha->valor;
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
    
    public function visitNegacionExpression(NegacionExpressionContext $ctx) {        
        return - $this->visit($ctx->expresion());
    }
}
