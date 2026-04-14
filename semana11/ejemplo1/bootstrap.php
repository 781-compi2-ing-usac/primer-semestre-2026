<?php

require_once __DIR__ . "/ANTLRv4/GrammarLexer.php";
require_once __DIR__ . "/ANTLRv4/GrammarParser.php";
require_once __DIR__ . "/ANTLRv4/GrammarVisitor.php";
require_once __DIR__ . "/ANTLRv4/GrammarBaseVisitor.php";
require_once __DIR__ . "/src/TypeChecker.php";
require_once __DIR__ . "/src/CompilerError.php";
require_once __DIR__ . "/src/Environment.php";
require_once __DIR__ . "/src/FlowTypes.php";
require_once __DIR__ . "/src/Invocable.php";
require_once __DIR__ . "/src/Foreigns.php";
require_once __DIR__ . "/src/Natives.php";
require_once __DIR__ . "/src/ARM/Constants.php";
require_once __DIR__ . "/src/ARM/ASMGenerator.php";
require_once __DIR__ . "/src/Compiler.php";
