grammar Grammar;

p
    : stmt* EOF                        # Program
    ;

stmt
    : 'print' '(' e ')' ';'            # PrintStatement
    ;

e    
    : e op=('*' | '/') e               # BinaryExpression
    | e op=('+' | '-') e               # BinaryExpression    
    | INT                              # PrimaryExpression
    | '-' e                            # UnaryExpression
    | '(' e ')'                        # GroupedExpression
    ;

INT : [0-9]+ ;
WS  : [ \t\r\n]+ -> skip ;
