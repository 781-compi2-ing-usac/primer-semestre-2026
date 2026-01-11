<?php

// AST
require_once __DIR__ . "/AST/Nodes.php";
require_once __DIR__ . "/AST/Visitor.php";
require_once __DIR__ . "/AST/Interpreter.php";
require_once __DIR__ . "/AST/Environment.php";

// Parser engine + parser generado
require_once "../lime/parse_engine.php";
require_once __DIR__ . "/grammar.class";

// Lexer
require_once __DIR__ . "/Lexer.php";
