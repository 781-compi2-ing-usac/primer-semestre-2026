<?php

/*
 * Generated from Grammar.g4 by ANTLR 4.13.2
 */

use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;

/**
 * This interface defines a complete generic visitor for a parse tree produced by {@see GrammarParser}.
 */
interface GrammarVisitor extends ParseTreeVisitor
{
	/**
	 * Visit a parse tree produced by {@see GrammarParser::p()}.
	 *
	 * @param Context\PContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitP(Context\PContext $context);

	/**
	 * Visit a parse tree produced by the `PrimaryExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 *
	 * @param Context\PrimaryExpressionContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitPrimaryExpression(Context\PrimaryExpressionContext $context);

	/**
	 * Visit a parse tree produced by the `BinaryExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 *
	 * @param Context\BinaryExpressionContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitBinaryExpression(Context\BinaryExpressionContext $context);

	/**
	 * Visit a parse tree produced by the `UnaryExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 *
	 * @param Context\UnaryExpressionContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitUnaryExpression(Context\UnaryExpressionContext $context);

	/**
	 * Visit a parse tree produced by the `GroupedExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 *
	 * @param Context\GroupedExpressionContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitGroupedExpression(Context\GroupedExpressionContext $context);
}