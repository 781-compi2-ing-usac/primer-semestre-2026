grammar Grammar;

p
    : stmt* EOF                        # Program
    ;

stmt
    : 'print' '(' e ')' ';'            # PrintStatement
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
WS  : [ \t\r\n]+ -> skip ;
