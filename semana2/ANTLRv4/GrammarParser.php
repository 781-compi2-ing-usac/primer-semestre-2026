<?php

/*
 * Generated from Grammar.g4 by ANTLR 4.13.2
 */

namespace {
	use Antlr\Antlr4\Runtime\Atn\ATN;
	use Antlr\Antlr4\Runtime\Atn\ATNDeserializer;
	use Antlr\Antlr4\Runtime\Atn\ParserATNSimulator;
	use Antlr\Antlr4\Runtime\Dfa\DFA;
	use Antlr\Antlr4\Runtime\Error\Exceptions\FailedPredicateException;
	use Antlr\Antlr4\Runtime\Error\Exceptions\NoViableAltException;
	use Antlr\Antlr4\Runtime\PredictionContexts\PredictionContextCache;
	use Antlr\Antlr4\Runtime\Error\Exceptions\RecognitionException;
	use Antlr\Antlr4\Runtime\RuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\TokenStream;
	use Antlr\Antlr4\Runtime\Vocabulary;
	use Antlr\Antlr4\Runtime\VocabularyImpl;
	use Antlr\Antlr4\Runtime\RuntimeMetaData;
	use Antlr\Antlr4\Runtime\Parser;

	final class GrammarParser extends Parser
	{
		public const T__0 = 1, T__1 = 2, T__2 = 3, T__3 = 4, T__4 = 5, T__5 = 6, 
               INT = 7, WS = 8;

		public const RULE_p = 0, RULE_e = 1;

		/**
		 * @var array<string>
		 */
		public const RULE_NAMES = [
			'p', 'e'
		];

		/**
		 * @var array<string|null>
		 */
		private const LITERAL_NAMES = [
		    null, "'*'", "'/'", "'+'", "'-'", "'('", "')'"
		];

		/**
		 * @var array<string>
		 */
		private const SYMBOLIC_NAMES = [
		    null, null, null, null, null, null, null, "INT", "WS"
		];

		private const SERIALIZED_ATN =
			[4, 1, 8, 29, 2, 0, 7, 0, 2, 1, 7, 1, 1, 0, 1, 0, 1, 0, 1, 1, 1, 1, 1, 
		    1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 3, 1, 16, 8, 1, 1, 1, 1, 1, 1, 1, 
		    1, 1, 1, 1, 1, 1, 5, 1, 24, 8, 1, 10, 1, 12, 1, 27, 9, 1, 1, 1, 0, 
		    1, 2, 2, 0, 2, 0, 2, 1, 0, 1, 2, 1, 0, 3, 4, 30, 0, 4, 1, 0, 0, 0, 
		    2, 15, 1, 0, 0, 0, 4, 5, 3, 2, 1, 0, 5, 6, 5, 0, 0, 1, 6, 1, 1, 0, 
		    0, 0, 7, 8, 6, 1, -1, 0, 8, 16, 5, 7, 0, 0, 9, 10, 5, 4, 0, 0, 10, 
		    16, 3, 2, 1, 2, 11, 12, 5, 5, 0, 0, 12, 13, 3, 2, 1, 0, 13, 14, 5, 
		    6, 0, 0, 14, 16, 1, 0, 0, 0, 15, 7, 1, 0, 0, 0, 15, 9, 1, 0, 0, 0, 
		    15, 11, 1, 0, 0, 0, 16, 25, 1, 0, 0, 0, 17, 18, 10, 5, 0, 0, 18, 19, 
		    7, 0, 0, 0, 19, 24, 3, 2, 1, 6, 20, 21, 10, 4, 0, 0, 21, 22, 7, 1, 
		    0, 0, 22, 24, 3, 2, 1, 5, 23, 17, 1, 0, 0, 0, 23, 20, 1, 0, 0, 0, 
		    24, 27, 1, 0, 0, 0, 25, 23, 1, 0, 0, 0, 25, 26, 1, 0, 0, 0, 26, 3, 
		    1, 0, 0, 0, 27, 25, 1, 0, 0, 0, 3, 15, 23, 25];
		protected static $atn;
		protected static $decisionToDFA;
		protected static $sharedContextCache;

		public function __construct(TokenStream $input)
		{
			parent::__construct($input);

			self::initialize();

			$this->interp = new ParserATNSimulator($this, self::$atn, self::$decisionToDFA, self::$sharedContextCache);
		}

		private static function initialize(): void
		{
			if (self::$atn !== null) {
				return;
			}

			RuntimeMetaData::checkVersion('4.13.2', RuntimeMetaData::VERSION);

			$atn = (new ATNDeserializer())->deserialize(self::SERIALIZED_ATN);

			$decisionToDFA = [];
			for ($i = 0, $count = $atn->getNumberOfDecisions(); $i < $count; $i++) {
				$decisionToDFA[] = new DFA($atn->getDecisionState($i), $i);
			}

			self::$atn = $atn;
			self::$decisionToDFA = $decisionToDFA;
			self::$sharedContextCache = new PredictionContextCache();
		}

		public function getGrammarFileName(): string
		{
			return "Grammar.g4";
		}

		public function getRuleNames(): array
		{
			return self::RULE_NAMES;
		}

		public function getSerializedATN(): array
		{
			return self::SERIALIZED_ATN;
		}

		public function getATN(): ATN
		{
			return self::$atn;
		}

		public function getVocabulary(): Vocabulary
        {
            static $vocabulary;

			return $vocabulary = $vocabulary ?? new VocabularyImpl(self::LITERAL_NAMES, self::SYMBOLIC_NAMES);
        }

		/**
		 * @throws RecognitionException
		 */
		public function p(): Context\PContext
		{
		    $localContext = new Context\PContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 0, self::RULE_p);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(4);
		        $this->recursiveE(0);
		        $this->setState(5);
		        $this->match(self::EOF);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function e(): Context\EContext
		{
			return $this->recursiveE(0);
		}

		/**
		 * @throws RecognitionException
		 */
		private function recursiveE(int $precedence): Context\EContext
		{
			$parentContext = $this->ctx;
			$parentState = $this->getState();
			$localContext = new Context\EContext($this->ctx, $parentState);
			$previousContext = $localContext;
			$startState = 2;
			$this->enterRecursionRule($localContext, 2, self::RULE_e, $precedence);

			try {
				$this->enterOuterAlt($localContext, 1);
				$this->setState(15);
				$this->errorHandler->sync($this);

				switch ($this->input->LA(1)) {
				    case self::INT:
				    	$localContext = new Context\PrimaryExpressionContext($localContext);
				    	$this->ctx = $localContext;
				    	$previousContext = $localContext;

				    	$this->setState(8);
				    	$this->match(self::INT);
				    	break;

				    case self::T__3:
				    	$localContext = new Context\UnaryExpressionContext($localContext);
				    	$this->ctx = $localContext;
				    	$previousContext = $localContext;
				    	$this->setState(9);
				    	$this->match(self::T__3);
				    	$this->setState(10);
				    	$this->recursiveE(2);
				    	break;

				    case self::T__4:
				    	$localContext = new Context\GroupedExpressionContext($localContext);
				    	$this->ctx = $localContext;
				    	$previousContext = $localContext;
				    	$this->setState(11);
				    	$this->match(self::T__4);
				    	$this->setState(12);
				    	$this->recursiveE(0);
				    	$this->setState(13);
				    	$this->match(self::T__5);
				    	break;

				default:
					throw new NoViableAltException($this);
				}
				$this->ctx->stop = $this->input->LT(-1);
				$this->setState(25);
				$this->errorHandler->sync($this);

				$alt = $this->getInterpreter()->adaptivePredict($this->input, 2, $this->ctx);

				while ($alt !== 2 && $alt !== ATN::INVALID_ALT_NUMBER) {
					if ($alt === 1) {
						if ($this->getParseListeners() !== null) {
						    $this->triggerExitRuleEvent();
						}

						$previousContext = $localContext;
						$this->setState(23);
						$this->errorHandler->sync($this);

						switch ($this->getInterpreter()->adaptivePredict($this->input, 1, $this->ctx)) {
							case 1:
							    $localContext = new Context\BinaryExpressionContext(new Context\EContext($parentContext, $parentState));
							    $this->pushNewRecursionContext($localContext, $startState, self::RULE_e);
							    $this->setState(17);

							    if (!($this->precpred($this->ctx, 5))) {
							        throw new FailedPredicateException($this, "\\\$this->precpred(\\\$this->ctx, 5)");
							    }
							    $this->setState(18);

							    $localContext->op = $this->input->LT(1);
							    $_la = $this->input->LA(1);

							    if (!($_la === self::T__0 || $_la === self::T__1)) {
							    	    $localContext->op = $this->errorHandler->recoverInline($this);
							    } else {
							    	if ($this->input->LA(1) === Token::EOF) {
							    	    $this->matchedEOF = true;
							        }

							    	$this->errorHandler->reportMatch($this);
							    	$this->consume();
							    }
							    $this->setState(19);
							    $this->recursiveE(6);
							break;

							case 2:
							    $localContext = new Context\BinaryExpressionContext(new Context\EContext($parentContext, $parentState));
							    $this->pushNewRecursionContext($localContext, $startState, self::RULE_e);
							    $this->setState(20);

							    if (!($this->precpred($this->ctx, 4))) {
							        throw new FailedPredicateException($this, "\\\$this->precpred(\\\$this->ctx, 4)");
							    }
							    $this->setState(21);

							    $localContext->op = $this->input->LT(1);
							    $_la = $this->input->LA(1);

							    if (!($_la === self::T__2 || $_la === self::T__3)) {
							    	    $localContext->op = $this->errorHandler->recoverInline($this);
							    } else {
							    	if ($this->input->LA(1) === Token::EOF) {
							    	    $this->matchedEOF = true;
							        }

							    	$this->errorHandler->reportMatch($this);
							    	$this->consume();
							    }
							    $this->setState(22);
							    $this->recursiveE(5);
							break;
						} 
					}

					$this->setState(27);
					$this->errorHandler->sync($this);

					$alt = $this->getInterpreter()->adaptivePredict($this->input, 2, $this->ctx);
				}
			} catch (RecognitionException $exception) {
				$localContext->exception = $exception;
				$this->errorHandler->reportError($this, $exception);
				$this->errorHandler->recover($this, $exception);
			} finally {
				$this->unrollRecursionContexts($parentContext);
			}

			return $localContext;
		}

		public function sempred(?RuleContext $localContext, int $ruleIndex, int $predicateIndex): bool
		{
			switch ($ruleIndex) {
					case 1:
						return $this->sempredE($localContext, $predicateIndex);

				default:
					return true;
				}
		}

		private function sempredE(?Context\EContext $localContext, int $predicateIndex): bool
		{
			switch ($predicateIndex) {
			    case 0:
			        return $this->precpred($this->ctx, 5);

			    case 1:
			        return $this->precpred($this->ctx, 4);
			}

			return true;
		}
	}
}

namespace Context {
	use Antlr\Antlr4\Runtime\ParserRuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;
	use Antlr\Antlr4\Runtime\Tree\TerminalNode;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;
	use GrammarParser;
	use GrammarVisitor;
	use GrammarListener;

	class PContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return GrammarParser::RULE_p;
	    }

	    public function e(): ?EContext
	    {
	    	return $this->getTypedRuleContext(EContext::class, 0);
	    }

	    public function EOF(): ?TerminalNode
	    {
	        return $this->getToken(GrammarParser::EOF, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->enterP($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->exitP($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof GrammarVisitor) {
			    return $visitor->visitP($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class EContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return GrammarParser::RULE_e;
	    }
	 
		public function copyFrom(ParserRuleContext $context): void
		{
			parent::copyFrom($context);

		}
	}

	class PrimaryExpressionContext extends EContext
	{
		public function __construct(EContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function INT(): ?TerminalNode
	    {
	        return $this->getToken(GrammarParser::INT, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->enterPrimaryExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->exitPrimaryExpression($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof GrammarVisitor) {
			    return $visitor->visitPrimaryExpression($this);
		    }

			return $visitor->visitChildren($this);
		}
	}

	class BinaryExpressionContext extends EContext
	{
		/**
		 * @var Token|null $op
		 */
		public $op;

		public function __construct(EContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    /**
	     * @return array<EContext>|EContext|null
	     */
	    public function e(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(EContext::class);
	    	}

	        return $this->getTypedRuleContext(EContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->enterBinaryExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->exitBinaryExpression($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof GrammarVisitor) {
			    return $visitor->visitBinaryExpression($this);
		    }

			return $visitor->visitChildren($this);
		}
	}

	class UnaryExpressionContext extends EContext
	{
		public function __construct(EContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function e(): ?EContext
	    {
	    	return $this->getTypedRuleContext(EContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->enterUnaryExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->exitUnaryExpression($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof GrammarVisitor) {
			    return $visitor->visitUnaryExpression($this);
		    }

			return $visitor->visitChildren($this);
		}
	}

	class GroupedExpressionContext extends EContext
	{
		public function __construct(EContext $context)
		{
		    parent::__construct($context);

		    $this->copyFrom($context);
	    }

	    public function e(): ?EContext
	    {
	    	return $this->getTypedRuleContext(EContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->enterGroupedExpression($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof GrammarListener) {
			    $listener->exitGroupedExpression($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof GrammarVisitor) {
			    return $visitor->visitGroupedExpression($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 
}