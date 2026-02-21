grammar Grammar;

// Tokens a ignorar
WS : [ \n\r\t\u000B\u000C\u0000]+				-> channel(HIDDEN) ;

Block_comment : '/*' (Block_comment|.)*? '*/'	-> channel(HIDDEN) ; // nesting comments allowed

Line_comment : '//' .*? ('\n'|EOF)				-> channel(HIDDEN) ;

p
    : stmt* EOF                        # Program
    ;

stmt
    : 'print' '(' expresion ')' ';'                # PrintStatement
    | 'var' ID '=' expresion ';'                  # VarDeclaration
    | ID '=' expresion ';'                        # AssignmentStatement
    | ifstmt                            # IFStatement
    | forstmt                           # ForStatement
    | 'break' ';'                          #BreakStatement
    ;

block
    : '{' stmt* '}' # BlockStatement
    ;



//if
// tomar en cuenta que en este caso unicamente aceptara TRUE y FALSE como valores, es necesario extender la funcionalidad
ifstmt
    : 'if' relationexpr block ('else' block)?                      # IFSolito
    ;

//for while
// tomar en cuenta que en este caso unicamente aceptara TRUE y FALSE como valores, es necesario extender la funcionalidad
// el bucle no saldra de si mismo asi que es necesario implementar una manera de break
// tomar en cuenta que el breakflow en este caso solamente puede ser hijo directo de un forstatement
// pero deberia aplicar a todos los hijos directos o indirectos del arbol
forstmt
    : 'for' relationexpr block # ForWhile
    ;


relationexpr
    :   BOOLEAN                 # BoolExpression
    ;

//Gramatica ambigua
expresion        
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
    : '(' expresion ')'                        # GroupedExpression   
    | INT                              # IntExpression
    | ID                               # ReferenceExpression
    ;

INT : [0-9]+ ;
BOOLEAN : 'TRUE'
        | 'FALSE'
        ;
ID  : [a-zA-Z_][a-zA-Z0-9_]* ;

//WS  : [ \t\r\n]+ -> skip ;
