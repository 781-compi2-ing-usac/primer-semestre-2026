<?php
class Lexer {
    private $input;
    private $pos = 0;
    private $length;
    private $line = 1;
    private $col = 1;

    public function __construct($input) {
        $this->input = $input;
        $this->length = strlen($input);
    }

    public function nextToken() {
        while ($this->pos < $this->length) {
            if (preg_match('/\G\s+/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                continue;
            }
            if (preg_match('/\G"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('string', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\s+/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                continue;
            }
            if (preg_match('/\Gprint/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('print', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gtrue/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('true', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gfalse/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('false', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gif/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('if', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gelse/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('else', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gwhile/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('while', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gcontinue/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('continue', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gbreak/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('break', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Greturn/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('return', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gfunc/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('func', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gvar/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('var', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gnew/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('new', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\Gclass/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('class', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G[a-zA-Z_][a-zA-Z0-9_]*/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('id', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G=/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'='", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G;/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("';'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G,/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("','", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\./A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'.'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G[0-9]+(\.[0-9]+)?/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array('num', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\+/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'+'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\-/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'-'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\*/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'*'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G</A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'<'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G>/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'>'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G!/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'!'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\(/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'('", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\)/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("')'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\{/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'{'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            if (preg_match('/\G\}/A', $this->input, $m, 0, $this->pos)) {
                $lexeme = $m[0];
                $start_line = $this->line;
                $start_col = $this->col;
                $lines = explode("\n", $lexeme);
                if (count($lines) > 1) {
                    $this->line += count($lines) - 1;
                    $this->col = strlen(end($lines)) + 1;
                } else {
                    $this->col += strlen($lexeme);
                }
                $this->pos += strlen($lexeme);
                $end_line = $this->line;
                $end_col = $this->col;
                return array("'}'", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));
            }
            throw new Exception('Lexical error at line ' . $this->line . ', col ' . $this->col);
        }
        return array('EOF', array('value' => null, 'location' => array('start'=>array('line'=>$this->line,'col'=>$this->col),'end'=>array('line'=>$this->line,'col'=>$this->col))));
    }
}
