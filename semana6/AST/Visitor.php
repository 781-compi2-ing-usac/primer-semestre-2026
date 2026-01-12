<?php
interface Visitor {
    public function visitExpression(Expression $expr);
    public function visitUnaryExpression(UnaryExpression $expr);
    public function visitBinaryExpression(BinaryExpression $expr);
    public function visitAgroupedExpression(AgroupedExpression $expr);
    public function visitNumberExpression(NumberExpression $expr);
    public function visitBooleanExpression(BooleanExpression $expr);
    public function visitStringExpression(StringExpression $expr);
    public function visitPrintStatement(PrintStatement $expr);
    public function visitVarDclStatement(VarDclStatement $expr);
    public function visitVarAssignStatement(VarAssignStatement $expr);
    public function visitRefVarStatement(RefVarStatement $expr);
    public function visitBlockStatement(BlockStatement $expr);
    public function visitIfStatement(IfStatement $expr);
    public function visitWhileStatement(WhileStatement $expr);
    public function visitFlowStatement(FlowStatement $expr);
    public function visitCallStatement(CallStatement $expr);
    public function visitFunctionDclStatement(FunctionDclStatement $expr);
    public function visitClassDclStatement(ClassDclStatement $expr);
    public function visitInstanceExpression(InstanceExpression $expr);
}