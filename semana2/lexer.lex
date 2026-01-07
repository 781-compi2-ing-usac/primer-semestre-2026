\s+                     /* skip whitespace */

[0-9]+(\.[0-9]+)?       return 'num';

[a-zA-Z_][a-zA-Z0-9_]*  return 'var';

"+"                     return '+';
"-"                     return '-';
"*"                     return '*';
"/"                     return '/';
"%"                     return '%';
"^"                     return '^';

"="                     return '=';

"("                     return '(';
")"                     return ')';

","                     return ',';

<<EOF>>                 return 'EOF';
