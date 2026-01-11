import re
import sys
from pathlib import Path

LEX_RULE = re.compile(
    r"""
    ^\s*
    (?P<regex>.+?)
    \s+
    (?:
        return\s+'(?P<token>[^']+)';
      |
        /\*.*?\*/
    )
    \s*$
    """,
    re.VERBOSE
)

def escape_php_regex(regex):
    return regex.replace('/', r'\/')

def parse_lex(path):
    rules = []

    for line in Path(path).read_text().splitlines():
        line = line.strip()
        if not line or line.startswith('#'):
            continue

        if line.startswith('<<EOF>>'):
            rules.append({
                'regex': None,
                'token': 'EOF',
                'skip': False,
                'eof': True,
                'literal': False
            })
            continue

        m = LEX_RULE.match(line)
        if not m:
            raise SyntaxError(f"Invalid lex rule: {line}")

        regex = m.group('regex')
        token = m.group('token')
        skip = token is None
        is_literal = regex.startswith('"') and regex.endswith('"')
        if is_literal:
            regex = re.escape(regex[1:-1])
        regex = escape_php_regex(regex)

        rules.append({
            'regex': regex,
            'token': token,
            'skip': skip,
            'eof': False,
            'literal': is_literal
        })

    return rules

def generate_php(rules, out_path):
    php = []

    php.append("<?php\n")
    php.append("class Lexer {\n")
    php.append("    private $input;\n")
    php.append("    private $pos = 0;\n")
    php.append("    private $length;\n")
    php.append("    private $line = 1;\n")
    php.append("    private $col = 1;\n\n")

    php.append("    public function __construct($input) {\n")
    php.append("        $this->input = $input;\n")
    php.append("        $this->length = strlen($input);\n")
    php.append("    }\n\n")

    php.append("    public function nextToken() {\n")
    php.append("        while ($this->pos < $this->length) {\n")
    php.append("            if (preg_match('/\\G\\s+/A', $this->input, $m, 0, $this->pos)) {\n")
    php.append("                $lexeme = $m[0];\n")
    php.append("                $lines = explode(\"\\n\", $lexeme);\n")
    php.append("                if (count($lines) > 1) {\n")
    php.append("                    $this->line += count($lines) - 1;\n")
    php.append("                    $this->col = strlen(end($lines)) + 1;\n")
    php.append("                } else {\n")
    php.append("                    $this->col += strlen($lexeme);\n")
    php.append("                }\n")
    php.append("                $this->pos += strlen($lexeme);\n")
    php.append("                continue;\n")
    php.append("            }\n")

    for r in rules:
        if r['eof']:
            continue

        php.append(
            "            if (preg_match('/\\G{}/A', $this->input, $m, 0, $this->pos)) {{\n"
            .format(r['regex'])
        )
        php.append("                $lexeme = $m[0];\n")
        php.append("                $start_line = $this->line;\n")
        php.append("                $start_col = $this->col;\n")
        php.append("                $lines = explode(\"\\n\", $lexeme);\n")
        php.append("                if (count($lines) > 1) {\n")
        php.append("                    $this->line += count($lines) - 1;\n")
        php.append("                    $this->col = strlen(end($lines)) + 1;\n")
        php.append("                } else {\n")
        php.append("                    $this->col += strlen($lexeme);\n")
        php.append("                }\n")
        php.append("                $this->pos += strlen($lexeme);\n")
        php.append("                $end_line = $this->line;\n")
        php.append("                $end_col = $this->col;\n")

        if r['skip']:
            php.append("                continue;\n")
        else:
            if r['literal'] and len(r['token']) == 1:
                php.append(
                    "                return array(\"'{}'\", array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));\n"
                    .format(r['token'])
                )
            else:
                php.append(
                    "                return array('{}', array('value' => $lexeme, 'location' => array('start' => array('line'=>$start_line,'col'=>$start_col), 'end' => array('line'=>$end_line,'col'=>$end_col))));\n"
                    .format(r['token'])
                )

        php.append("            }\n")

    php.append("            throw new Exception('Lexical error at line ' . $this->line . ', col ' . $this->col);\n")
    php.append("        }\n")
    php.append("        return array('EOF', array('value' => null, 'location' => array('start'=>array('line'=>$this->line,'col'=>$this->col),'end'=>array('line'=>$this->line,'col'=>$this->col))));\n")
    php.append("    }\n")
    php.append("}\n")

    Path(out_path).write_text("".join(php))

if __name__ == '__main__':
    if len(sys.argv) != 3:
        print("usage: lexer_gen.py input.lex Lexer.php")
        sys.exit(1)

    rules = parse_lex(sys.argv[1])
    generate_php(rules, sys.argv[2])
