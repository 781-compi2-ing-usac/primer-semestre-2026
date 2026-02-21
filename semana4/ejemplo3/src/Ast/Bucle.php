<?php

namespace App\Ast;

use Context\ForStatementContext;
use Context\ForWhileContext;

trait Bucle {

    public function visitForStatement(ForStatementContext $ctx) {
        $value = $this->visit($ctx->forstmt());
        return $value;
    }

    public function visitForWhile(ForWhileContext $ctx) {
        //en este caso se controla el limite del for de manera interna,
        //esta limitante deberia implementarse desde el input de golampi
        $ConditionalExpr = $this->visit($ctx->relationexpr());
        
        $limitrecursion = 0;
        while ($ConditionalExpr){
            //es necesario tomar en cuenta la implementacion de los entornos locales
            // si no se esta manejando en los bloques de codigo. aka blockbucle
            $this->console .= "entro al for, repeticion".$limitrecursion . "\n";
            $breakValue = $this->visit($ctx->block() );  
             $this->console .= $breakValue;
            if ($breakValue == "BreakOrder" || $limitrecursion > 10) {
                break;
            }
            $limitrecursion++;
            
        }
    }
}