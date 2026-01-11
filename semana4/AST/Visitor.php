<?php
interface Visitor {
    public function visitExpression(Expression $expr);
    public function visitUnaryExpression(UnaryExpression $expr);
    public function visitBinaryExpression(BinaryExpression $expr);
    public function visitAgroupedExpression(AgroupedExpression $expr);
    public function visitNumberExpression(NumberExpression $expr);
    public function visitBooleanExpression(BooleanExpression $expr);
    public function visitPrintStatement(PrintStatement $expr);
    public function visitVarDclStatement(VarDclStatement $expr);
    public function visitRefVarStatement(RefVarStatement $expr);
    public function visitBlockStatement(BlockStatement $expr);
    public function visitIfStatement(IfStatement $expr);
}