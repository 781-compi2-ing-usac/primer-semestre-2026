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
    | 'var' tipos ID '=' expresion ';'                  # VarDeclaration
    | ID '=' expresion ';'                        # AssignmentStatement
    | '{' stmt* '}'                    # BlockStatement
    ;

//Gramatica ambigua
expresion
    : primary #PrimitivoExpression
    | ID                               # ReferenceExpression
    | '(' expresion ')'                        # GroupedExpression
    | '-' expresion                            # NegacionExpression
    | expresion op=('*' | '/') expresion               # AritmeticaExpression
    | expresion op=('+' | '-') expresion               # AritmeticaExpression
    ;

tipos 
    : 'String'
    | 'Int'
    | 'Float'
    | 'Bool'
    | 'Character'
    | ID
    ;

primary    
    : INT                              # IntExpression
    | FLOAT # FloatExpresion
    ;

INT : [0-9]+ ;
FLOAT : INT ('.' [0-9]+)?;
ID  : [a-zA-Z_][a-zA-Z0-9_]* ;
//WS  : [ \t\r\n]+ -> skip ;
