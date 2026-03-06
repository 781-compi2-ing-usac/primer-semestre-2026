grammar Grammar;

p
    : (stmt NEWLINE*)* EOF                # Program
    ;

stmt
    : 'print' '(' e ')' NEWLINE           # PrintStatement
    ;

e    
    : add                              
    ;

add 
    : add op=('+' | '-') prod          # AddExpression
    | prod                             # AddExpression
    ;

prod
    : prod op=('*' | '/') unary        # ProductExpression
    | unary                            # ProductExpression
    ;

unary
    : primary                          # PrimaryExpression
    | '-' unary                        # UnaryExpression
    ;

primary    
    : '(' e ')'                        # GroupedExpression   
    | INT                              # IntExpression
    ;

INT : [0-9]+ ;
NEWLINE : '\n' ;
WS  : [ \t\r]+ -> skip ;
