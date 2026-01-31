grammar Grammar;

p
    : e EOF
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
