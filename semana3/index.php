<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Parser Playground</title>
        <link rel="stylesheet" href="/static/style.css">
    </head>
    <body>
        <?php
            $input = "";
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $input = $_POST["expression"];        
            }
        ?>
        <h2>Entrada</h2>

        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="editor-container">   
                <div class="line-numbers" id="lineNumbers">1</div>
                <textarea id="editor" name="expression" placeholder="Escriba su código aquí..."><? echo htmlspecialchars($input)?></textarea>
            </div>
            <input type="submit" value="Run">
        </form>

        <h2>Salida:</h2>
        <div class="console">
            <?php
            require_once("bootstrap.php");
            
            $parser = new parse_engine(new grammar());

            if (!empty($input)) {
                try {
                    $parser->reset();
                    $lexer = new Lexer($input);
                    while (true) {
                        $token = $lexer->nextToken();
                        $tok = $token[0];
                        $val = $token[1];
                        if ($tok == "EOF") {
                            $ast = $parser->eat_eof();
                            break;
                        }
                        $parser->eat($tok, $val);
                    }
                    $interpreter = new Interpreter();
                    foreach ($ast as $stmt) {
                        $stmt->accept($interpreter);
                    }
                    $output = str_replace(["\r\n", "\r"], "\n", $interpreter->output);

                    // quitar espacios al inicio de cada línea
                    $output = preg_replace('/^[ \t]+/m', '', $output);

                    echo htmlspecialchars($output);                    
                } catch(Exception $e) {
                    echo "Error: " . htmlspecialchars($e->getMessage());
                }
            } else {
                echo "Por favor ingrese código para parsear.";
            }
            ?>
        </div>
        <script src="/static/script.js"></script>
    </body>
</html>
