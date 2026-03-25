<?php

namespace App\Ast\Expresiones;

use Context\FunctionCallExpressionContext;
use Context\FunctionCallStmtContext;
use App\Env\Result;

trait FunctionCall
{
    public function visitFunctionCallExpression(FunctionCallExpressionContext $ctx)
    {
        return $this->handleFunctionCall($ctx->ID()->getText(), $ctx->args());
    }

    public function visitcallFunctionStmt(FunctionCallStmtContext $ctx)
    {
        return $this->handleFunctionCall($ctx->ID()->getText(), $ctx->args());
    }

    private function handleFunctionCall($funcName, $argsCtx)
    {
        $symbol = $this->env->get($funcName);
        
        if ($symbol->clase !== \App\Env\Symbol::CLASE_FUNCION) {
            throw new \Exception("'$funcName' no es una función.");
        }
        
        $args = [];
        if ($argsCtx !== null) {
            $args = $this->visit($argsCtx);
        }
        
        if (count($symbol->params) !== count($args)) {
            throw new \Exception("Número incorrecto de argumentos para '$funcName'.");
        }
        
        $this->asmGenerador->comment("Llamada a función: " . $funcName);
        
        $paramCount = count($args);
        $this->asmGenerador->subi("sp", "sp", ($paramCount + 1) * 16);
        
        $argIndex = 0;
        foreach ($args as $arg) {
            $this->visit($arg);
            $offset = ($paramCount - $argIndex) * 16;
            $argReg = $this->stack->popValue();
            $this->asmGenerador->str($argReg, "sp", $offset);
            $argIndex++;
        }
        
        $funcLabel = $this->asmGenerador->getFunctionLabel($funcName);
        $this->asmGenerador->bl($funcLabel);
        
        $this->asmGenerador->mov("x9", "x0");
        $this->asmGenerador->addi("sp", "sp", ($paramCount + 1) * 16);
        
        $this->stack->pushValue("x9");
        
        return Result::stack(Result::INT, $this->stack->getStackOffset());
    }
}
