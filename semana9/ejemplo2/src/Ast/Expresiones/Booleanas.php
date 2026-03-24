<?php

namespace App\Ast\Expresiones;

use Context\RelacionalExpresionContext;
use Context\EqualityExpressionContext;
use Context\AndExpressionContext;
use Context\OrExpressionContext;
use Context\NotExpressionContext;
use Context\BoolTrueExpressionContext;
use Context\BoolFalseExpressionContext;
use App\Env\Result;

trait Booleanas
{
    public function visitRelacionalExpresion(RelacionalExpresionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));
        
        $op = $ctx->op->getText();
        $this->asmGenerador->comment("Relacional: " . $op);

        $this->regs->pop();
        $this->asmGenerador->ldr("x1", "sp");
        $this->regs->pop();
        $this->asmGenerador->cmp("x1", "x0");

        $cond = match($op) {
            '<' => 'lt',
            '<=' => 'le',
            '>' => 'gt',
            '>=' => 'ge',
            default => 'lt',
        };

        $this->asmGenerador->cset("x0", $cond);
        $this->regs->push(0);
        return Result::stack(Result::INT, 0);
    }

    public function visitEqualityExpression(EqualityExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));
        
        $op = $ctx->op->getText();
        $this->asmGenerador->comment("Igualdad: " . $op);

        $this->regs->pop();
        $this->asmGenerador->ldr("x1", "sp");
        $this->regs->pop();
        $this->asmGenerador->cmp("x1", "x0");

        $cond = ($op == "==") ? 'eq' : 'ne';
        $this->asmGenerador->cset("x0", $cond);
        $this->regs->push(0);
        return Result::stack(Result::INT, 0);
    }

    public function visitAndExpression(AndExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));

        $this->asmGenerador->comment("AND");
        $this->regs->pop();
        $this->asmGenerador->ldr("x1", "sp");
        $this->regs->pop();
        $this->asmGenerador->and_op("x0", "x1", "x0");
        $this->regs->push(0);
        
        return Result::stack(Result::INT, 0);
    }

    public function visitOrExpression(OrExpressionContext $ctx) {
        $leftResult = $this->visit($ctx->expresion(0));
        $rightResult = $this->visit($ctx->expresion(1));

        $this->asmGenerador->comment("OR");
        $this->regs->pop();
        $this->asmGenerador->ldr("x1", "sp");
        $this->regs->pop();
        $this->asmGenerador->orr("x0", "x1", "x0");
        $this->regs->push(0);
        
        return Result::stack(Result::INT, 0);
    }

    public function visitNotExpression(NotExpressionContext $ctx) {
        $operandResult = $this->visit($ctx->expresion());

        $this->asmGenerador->comment("NOT");
        $this->regs->pop();
        $this->asmGenerador->mvn("x0", "x0");
        $this->regs->push(0);
        
        return Result::stack(Result::INT, 0);
    }

    public function visitBoolTrueExpression(BoolTrueExpressionContext $ctx) {
        $this->asmGenerador->comment("true");
        return $this->regs->push(1);
    }

    public function visitBoolFalseExpression(BoolFalseExpressionContext $ctx) {
        $this->asmGenerador->comment("false");
        return $this->regs->push(0);
    }
}
