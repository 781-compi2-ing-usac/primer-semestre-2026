%string

\s+                     /* skip whitespace */

"print"                 return 'print';
"true"                  return 'true';
"false"                 return 'false';
"if"                    return 'if';
"else"                  return 'else';
"while"                 return 'while';
"continue"              return 'continue';
"break"                 return 'break';
"return"                return 'return';
"func"                  return 'func';
"var"                   return 'var';
"new"                   return 'new';

[a-zA-Z_][a-zA-Z0-9_]*  return 'id';
"="                     return '=';
";"                     return ';';
","                     return ',';
"."                     return '.';

[0-9]+(\.[0-9]+)?       return 'num';

"+"                     return '+';
"-"                     return '-';
"*"                     return '*';

"<"                     return '<';
">"                     return '>';
"!"                     return '!';

"("                     return '(';
")"                     return ')';
"{"                     return '{';
"}"                     return '}';
"["                     return '[';
"]"                     return ']';

<<EOF>>                 return 'EOF';
