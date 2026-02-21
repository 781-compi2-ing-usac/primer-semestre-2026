<?php

namespace App\Ast;

use Context\BreakStatementContext;

trait Transferencia
{
    public function visitBreakStatement(BreakStatementContext $ctx) {
        return "BreakOrder";
    }
}