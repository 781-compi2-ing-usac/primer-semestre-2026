grammar Grammar;

p
    : stmt* EOF                        # Program
    ;

stmt
    : 'print' '(' e ')'                # PrintStatement
    | 'var' ID '=' e                   # VarDeclaration
    | '{' stmt* '}'                    # BlockStatement
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
    | ID                               # ReferenceExpression
    ;

INT : [0-9]+ ;
ID  : [a-zA-Z_][a-zA-Z0-9_]* ;
WS  : [ \t\r\n]+ -> skip ;
