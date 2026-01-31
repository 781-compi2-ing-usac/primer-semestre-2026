<?php

/*
 * Generated from Grammar.g4 by ANTLR 4.13.2
 */

use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;

/**
 * This interface defines a complete listener for a parse tree produced by
 * {@see GrammarParser}.
 */
interface GrammarListener extends ParseTreeListener {
	/**
	 * Enter a parse tree produced by {@see GrammarParser::p()}.
	 * @param $context The parse tree.
	 */
	public function enterP(Context\PContext $context): void;
	/**
	 * Exit a parse tree produced by {@see GrammarParser::p()}.
	 * @param $context The parse tree.
	 */
	public function exitP(Context\PContext $context): void;
	/**
	 * Enter a parse tree produced by the `PrimaryExpression`
	 * labeled alternative in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function enterPrimaryExpression(Context\PrimaryExpressionContext $context): void;
	/**
	 * Exit a parse tree produced by the `PrimaryExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function exitPrimaryExpression(Context\PrimaryExpressionContext $context): void;
	/**
	 * Enter a parse tree produced by the `BinaryExpression`
	 * labeled alternative in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function enterBinaryExpression(Context\BinaryExpressionContext $context): void;
	/**
	 * Exit a parse tree produced by the `BinaryExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function exitBinaryExpression(Context\BinaryExpressionContext $context): void;
	/**
	 * Enter a parse tree produced by the `UnaryExpression`
	 * labeled alternative in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function enterUnaryExpression(Context\UnaryExpressionContext $context): void;
	/**
	 * Exit a parse tree produced by the `UnaryExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function exitUnaryExpression(Context\UnaryExpressionContext $context): void;
	/**
	 * Enter a parse tree produced by the `GroupedExpression`
	 * labeled alternative in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function enterGroupedExpression(Context\GroupedExpressionContext $context): void;
	/**
	 * Exit a parse tree produced by the `GroupedExpression` labeled alternative
	 * in {@see GrammarParser::e()}.
	 * @param $context The parse tree.
	 */
	public function exitGroupedExpression(Context\GroupedExpressionContext $context): void;
}