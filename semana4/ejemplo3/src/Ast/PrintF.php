<?php

namespace App\Ast;

use Context\PrintStatementContext;

trait PrintF
{
    public function visitPrintStatement(PrintStatementContext $ctx) {
        $value = $this->visit($ctx->expresion());           
        $this->console .= $value . "\n";
        return $value;
    }
}