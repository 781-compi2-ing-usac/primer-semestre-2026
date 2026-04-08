<?php

namespace App\Ast\Sentencias;

use Context\FunctionDeclarationContext;
use Context\ParameterListContext;
use Context\ArgumentListContext;
use App\Env\Symbol;
use App\Env\Result;

trait FuncionDeclaracion
{
    public $functions = [];
    private $currentFunctionContext = null;
    private $returnLabel = null;
    private $paramCount = 0;

    public function visitFunctionDeclaration(FunctionDeclarationContext $ctx)
    {
        $funcName = $ctx->ID()->getText();
        $returnType = $ctx->tipos()->getText();
        $block = $ctx->block();
        $params = [];
        
        if ($ctx->params() !== null) {
            $params = $this->visit($ctx->params());
        }
        
        $paramCount = count($params);
        $localVarCount = $this->countLocalVars($block);
        $totalSlots = 2 + $paramCount + $localVarCount;
        
        $funcSymbol = new Symbol($returnType, $block, Symbol::CLASE_FUNCION, 0, 0);
        $funcSymbol->params = $params;
        $funcSymbol->paramCount = $paramCount;
        $funcSymbol->localVarCount = $localVarCount;
        $funcSymbol->totalSlots = $totalSlots;
        
        $this->env->set($funcName, $funcSymbol);
        
        $funcLabel = "func_" . $funcName;
        $this->asmGenerador->registerFunction($funcName, $funcLabel);
        
        return Result::buildVacio();
    }

    private function countLocalVars($block) {
        $count = 0;
        foreach ($block->stmt() as $stmt) {
            if ($stmt instanceof \Context\VarDeclarationContext) {
                $count++;
            }
        }
        return $count;
    }

    public function visitParameterList(ParameterListContext $ctx) {
        $params = [];
        $index = 0;
        foreach ($ctx->ID() as $id) {
            $params[] = new Parametro($id->getText(), $ctx->tipos($index)->getText(), $index);
            $index++;
        }
        return $params;
    }

    public function visitArgumentList(ArgumentListContext $ctx) {
        $args = [];
        foreach ($ctx->expresion() as $expr) {
            $args[] = $expr;
        }
        return $args;
    }
}

class Parametro {
    public $id;
    public $tipo;
    public $position;

    public function __construct($id, $tipo, $position) {
        $this->id = $id;
        $this->tipo = $tipo;
        $this->position = $position;
    }
}
