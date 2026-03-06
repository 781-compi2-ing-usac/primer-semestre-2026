<?php 

use Context\PrintStatementContext;

trait PrintHandler {

    public function visitPrintStatement(PrintStatementContext $ctx) {
        $value = $this->visit($ctx->e());        
        $this->console .= $value . "\n";
        return $value;
    }

}