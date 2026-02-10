<?php


namespace App\Ast\Expresiones;

use Context\AritmeticaExpressionContext;
use Context\NegacionExpressionContext;


trait Aritmeticas
{
    public function visitAritmeticaExpression(AritmeticaExpressionContext $ctx) {
        $izquierda = $this->visit($ctx->expresion(0));
        $derecha = $this->visit($ctx->expresion(1));
        $op = $ctx->op->getText();

        switch ($op) {
            case '+':
                return $izquierda + $derecha;
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
