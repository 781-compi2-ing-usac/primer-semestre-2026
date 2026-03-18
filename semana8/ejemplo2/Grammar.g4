grammar Grammar;

// Tokens a ignorar
WS : [ \n\r\t\u000B\u000C\u0000]+				-> channel(HIDDEN) ;

Block_comment : '/*' (Block_comment|.)*? '*/'	-> channel(HIDDEN) ;

Line_comment : '//' .*? ('\n'|EOF)				-> channel(HIDDEN) ;

p
    : stmt* EOF                        # Program
    ;

stmt
    : 'print' '(' expresion ')' ';'               # PrintStatement
    | 'var' tipos ID '=' expresion ';'            # VarDeclaration
    | ID '=' expresion ';'                        # AssignmentStatement
    | 'if' '(' expresion ')' block else?          # IfStatement
    | 'return' expresion? ';'                     # ReturnStatement
    | 'func' tipos ID '(' params? ')' block       # FunctionDeclaration
    | llamadas_funciones                          # callFunctionStmt
    ;

block
    : '{' stmt* '}'                        # BlockStatement
    ;

else
    : 'else' block
    ;

llamadas_funciones
    : ID '(' args? ')'                 # FunctionCallExpression
    ;

params
    : tipos ID (',' tipos ID)*                      # ParameterList
    ;

args
    : expresion (',' expresion)*      # ArgumentList
    ;

//Gramatica ambigua - precedencia manejada por ANTLR
expresion
    : primary                             # PrimitivoExpression
    | ID                                  # ReferenceExpression
    | llamadas_funciones                  # callFunction
    | '(' expresion ')'                   # GroupedExpression
    | '!' expresion                       # NotExpression
    | '-' expresion                       # NegacionExpression
    | expresion op=('*' | '/') expresion  # AritmeticaExpression
    | expresion op=('+' | '-') expresion  # AritmeticaExpression
    | expresion op=('<'|'<='|'>='|'>') expresion  # RelacionalExpresion
    | expresion op=('=='|'!=') expresion # EqualityExpression
    | expresion '&&' expresion            # AndExpression
    | expresion '||' expresion            # OrExpression
    | 'true'                             # BoolTrueExpression
    | 'false'                            # BoolFalseExpression
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
    | FLOAT                            # FloatExpression
    ;

INT : [0-9]+ ;
FLOAT : INT ('.' [0-9]+)? ;

ID  : [a-zA-Z_][a-zA-Z0-9_]* ;
