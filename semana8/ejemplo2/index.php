<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Parser Playground</title>
    <link rel="stylesheet" href="/static/style.css">
</head>
<body>

<?php
require __DIR__ . '/vendor/autoload.php';

use Antlr\Antlr4\Runtime\InputStream;
use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\Error\BailErrorStrategy;
use Antlr\Antlr4\Runtime\Error\Exceptions\ParseCancellationException;
use Antlr\Antlr4\Runtime\Error\Exceptions\InputMismatchException;

use App\Interpreter;

$input  = "";
$output = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = $_POST["expression"] ?? "";

    if (!empty($input)) {
        try {
            $inputStream = InputStream::fromString($input);

            $lexer  = new GrammarLexer($inputStream);
            $tokens = new CommonTokenStream($lexer);
            $parser = new GrammarParser($tokens);

            $parser->setErrorHandler(new BailErrorStrategy());

            // Regla inicial
            $tree = $parser->p();

            $interpreter = new Interpreter();
            $output = $interpreter->visit($tree);

            // Normalizar saltos de línea
            $output = str_replace(["\r\n", "\r"], "\n", $output);
            $output = preg_replace('/^[ \t]+/m', '', $output);

        } catch (ParseCancellationException $e) {
            $cause = $e->getPrevious();

            if ($cause instanceof InputMismatchException) {
                $offending = $cause->getOffendingToken();
                $expected  = $cause->getExpectedTokens();

                $found = $offending ? $offending->getText() : 'EOF';

                $parserObj = $cause->getRecognizer();
                $vocab = $parserObj->getVocabulary();

                $expectedNames = [];
                foreach ($expected->toArray() as $t) {
                    $expectedNames[] = $vocab->getDisplayName($t);
                }

                $output = sprintf(
                    "Error sintáctico en línea %d, columna %d: se esperaba %s y se encontró %s %s",
                    $offending->getLine(),
                    $offending->getCharPositionInLine(),
                    implode(" o ", $expectedNames),
                    $found, 
                    $vocab->getDisplayName($offending->getType())
                );
            } else {
                $output = $e;
            }
        } catch (Exception $e) {
            $output = $e->getMessage();
        }
    } else {
        $output = "Por favor ingrese código para parsear.";
    }
}
?>

<h2>Entrada</h2>

<form method="post">
    <div class="editor-container">
        <div class="line-numbers" id="lineNumbers">1</div>
        <textarea
            id="editor"
            name="expression"
            placeholder="Escriba su código aquí..."
        ><?php echo htmlspecialchars($input); ?></textarea>
    </div>

    <input type="submit" value="Run">
</form>

<h2>Salida:</h2>

<div class="console">
    <?php echo htmlspecialchars($output); ?>
</div>

<script src="/static/script.js"></script>
</body>
</html>
