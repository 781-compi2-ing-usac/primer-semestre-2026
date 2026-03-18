<?php

namespace App\Ast;

use Context\PrintStatementContext;
use App\Env\Result;

trait PrintF
{
    public function visitPrintStatement(PrintStatementContext $ctx) {
        $result = $this->visit($ctx->expresion());
        $this->console .= $result->valor . "\n";
        return Result::buildVacio();
    }
}