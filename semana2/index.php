<?php

require __DIR__ . '/vendor/autoload.php';
require_once 'bootstrap.php';

use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\CommonTokenStream;

$input = InputStream::fromString('3 + 5 * (2 - 8)');

$lexer = new GrammarLexer($input);
$tokens = new CommonTokenStream($lexer);
$parser = new GrammarParser($tokens);

$tree = $parser->p();

$interpreter = new Interpreter();
$result = $interpreter->visit($tree);
echo "Result: " . $result . PHP_EOL;