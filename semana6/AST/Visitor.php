<?php
interface Visitor {
    public function visitNode(Node $node);
    public function visitUnaryExpression(UnaryExpression $node);
    public function visitBinaryExpression(BinaryExpression $node);
    public function visitAgroupedExpression(AgroupedExpression $node);
    public function visitNumberExpression(NumberExpression $node);
    public function visitBooleanExpression(BooleanExpression $node);
    public function visitStringExpression(StringExpression $node);
    public function visitPrintStatement(PrintStatement $node);
    public function visitVarDclStatement(VarDclStatement $node);
    public function visitVarAssignStatement(VarAssignStatement $node);
    public function visitRefVarStatement(RefVarStatement $node);
    public function visitBlockStatement(BlockStatement $node);
    public function visitIfStatement(IfStatement $node);
    public function visitWhileStatement(WhileStatement $node);
    public function visitFlowStatement(FlowStatement $node);
    public function visitCallStatement(CallStatement $node);
    public function visitFunctionDclStatement(FunctionDclStatement $node);    
    public function visitArrayExpression(ArrayExpression $node);
}