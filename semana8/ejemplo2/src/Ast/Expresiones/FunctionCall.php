<?php

namespace App\Ast\Expresiones;

use Context\FunctionCallExpressionContext;
use Context\ParameterListContext;
use Context\ArgumentListContext;
use App\Env\{Result, Symbol};
use App\Env\Environment;

trait FunctionCall
{
    public function visitFunctionCallExpression(FunctionCallExpressionContext $context)
	{
	    $symbol = $this->env->get($context->ID()->getText());
        if ($symbol->clase != Symbol::CLASE_FUNCION) {
            error_log("No es una función.");
            return Result::buildVacio();
        }
        $argumentos = $this->visit($context->args());
        if(count($symbol->params) != count($argumentos)) {
            error_log("El numero de parametros no coincide");
            return Result::buildVacio();
        }
        $newEnv = new Environment($this->envGlobal);
        $i = 0;
        foreach($symbol->params as $param) {
            $result = $this->visit($argumentos[$i]);
            if ($param->tipo == $result->tipo) {
                $symbolVariable = new Symbol($result->tipo, $result->valor, Symbol::CLASE_VARIABLE, 0, 0);
                $newEnv->set($param->id, $symbolVariable);
            } else {
                error_log("Los tipos no coinciden, parametros incorrectos");
                return Result::buildVacio();
            }
            $i++;
        }

        $envBeforeCall = $this->env;
        $this->env = $newEnv;

        $bloque = $symbol->valor;
        $result = $this->visit($bloque);
        $this->env = $envBeforeCall;

        if($result->tipo != $symbol->tipo) {
            error_log("La expresion del return no coincide con el tipo de la funcion.");
            return Result::buildVacio();
        }
        return $result;
	}

    public function visitParameterList(ParameterListContext $ctx) {
        $params = array();
        $index = 0;
        foreach ($ctx->ID() as $id) {
            $params[] = new ParametrosFunction($id->getText(), $ctx->tipos($index)->getText());
            $index++;
        }
        return $params;
    }

    public function visitArgumentList(ArgumentListContext $ctx) {
        return $ctx->expresion();
    }
}

class ParametrosFunction
{
    public $id;
    public $tipo;

    public function __construct($id, $tipo)
    {
        $this->id = $id;
        $this->tipo = $tipo;
    }
}