<?php

namespace App\Ast;

use Context\IFStatementContext;
use Context\IFSolitoContext;

trait Control {

    public function visitIFStatement(IFStatementContext $ctx) {
        $value = $this->visit($ctx->ifstmt());
        //$value = $this->visit($ctx->expresion());
        return $value;
    }

    public function visitIFSolito(IFSolitoContext $ctx) {
        //en este caso la logica aplica unicamente para un if sin else
        $ConditionalExpr = $this->visit($ctx->relationexpr());
        //$value = $this->visit($ctx->expresion());
        $this->console .= $ConditionalExpr . "\n";
        if ($ConditionalExpr){
            //es necesario tomar en cuenta la implementacion de los entornos locales
            // si no se esta manejando en los bloques de codigo. aka blockbucle
            $this->console .= "entro al if" . "\n";
            $this->visit($ctx->block(0) );   
        } else {
            $this->console .= "este es el else" . "\n";
            $elseblock = $ctx->block(1);
            if (!is_null($elseblock)){
                $this->visit($elseblock);
            }
        }
    }
}