<?php

require __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/GrammarLexer.php';
require_once __DIR__ . '/GrammarParser.php';
require_once __DIR__ . '/GrammarVisitor.php';
require_once __DIR__ . '/GrammarBaseVisitor.php';
require_once __DIR__ . '/Interpreter.php';

use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\CommonTokenStream;

$input = InputStream::fromString('3 + 5 * (2 + 8)');

$lexer = new GrammarLexer($input);
$tokens = new CommonTokenStream($lexer);
$parser = new GrammarParser($tokens);

$tree = $parser->l();

$interpreter = new Interpreter();
$result = $interpreter->visit($tree);
echo "Result: " . $result . PHP_EOL;