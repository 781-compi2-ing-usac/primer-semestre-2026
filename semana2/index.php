<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parser Playground</title>
</head>
<body>

<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'bootstrap.php';

use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\Error\BailErrorStrategy;
use Antlr\Antlr4\Runtime\Error\Exceptions\ParseCancellationException;
use Antlr\Antlr4\Runtime\Error\Exceptions\InputMismatchException;


$input = "";
$output = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = $_POST["expression"] ?? "";

    if (!empty($input)) {
        try {
            // Crear stream de entrada
            $inputStream = InputStream::fromString($input);
            
            // Lexer y Parser
            $lexer  = new GrammarLexer($inputStream);
            $tokens = new CommonTokenStream($lexer);
            $parser = new GrammarParser($tokens);            
            
            $parser->setErrorHandler(new BailErrorStrategy());

            // Regla inicial
            $tree = $parser->p();
            

            // Interpretar
            $interpreter = new Interpreter();
            $result = $interpreter->visit($tree);

            $output = "Result: " . $result;
        } catch (ParseCancellationException $e) {
            $cause = $e->getPrevious();

            if ($cause instanceof InputMismatchException) {
                $offending = $cause->getOffendingToken();
                $expected  = $cause->getExpectedTokens();

                $found = $offending ? $offending->getText() : 'EOF';

                // Convertir expected tokens a texto
                $parser = $cause->getRecognizer();
                $vocab  = $parser->getVocabulary();

                $expectedNames = [];
                foreach ($expected->toArray() as $t) {
                    $name = $vocab->getDisplayName($t);
                    $expectedNames[] = $name;
                }

                $output = sprintf(
                    "Error sintáctico en línea %d, columna %d: se esperaba %s y se encontró %s",
                    $offending->getLine(),
                    $offending->getCharPositionInLine(),
                    implode(" o ", $expectedNames),
                    $found
                );
            } else {
                $output = "Error sintáctico.";
            }
        }
    } else {
        $output = "Ingrese una expresión.";
    }
}
?>

<h2>Entrada</h2>

<form method="post">
    <textarea name="expression" rows="5" cols="40"
        placeholder="Ej: 3 + 5 * (2 - 8)"><?php
        echo htmlspecialchars($input);
    ?></textarea>
    <br><br>
    <input type="submit" value="Run">
</form>

<h2>Salida</h2>

<pre><?php echo htmlspecialchars($output); ?></pre>

</body>
</html>