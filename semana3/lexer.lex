\s+                     /* skip whitespace */

"print"                 return 'print';

"var"                   return 'var';
[a-zA-Z_][a-zA-Z0-9_]*  return 'id';
"="                     return '=';
";"                     return ';';

[0-9]+(\.[0-9]+)?       return 'num';

"+"                     return '+';
"-"                     return '-';
"*"                     return '*';

"("                     return '(';
")"                     return ')';
"{"                     return '{';
"}"                     return '}';

<<EOF>>                 return 'EOF';
