grammar Grammar;

// Tokens a ignorar
WS : [ \n\r\t\u000B\u000C\u0000]+				-> channel(HIDDEN) ;

Block_comment : '/*' (Block_comment|.)*? '*/'	-> channel(HIDDEN) ; // nesting comments allowed

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
    | 'func' tipos ID '(' params? ')' block             # FunctionDeclaration //casi implementar
    | llamadas_funciones                      # callFunctionStmt //no implementar
    ;

block
    : '{' stmt* '}'                        # BlockStatement
    ;

else
    : 'else' block
    ;

llamadas_funciones
    : ID '(' args? ')'                 # FunctionCallExpression //implementar
    ;

params
    : tipos ID (',' tipos ID)*                      # ParameterList //implementar
    ;

args
    : expresion (',' expresion)*      # ArgumentList //implementar
    ;

//Gramatica ambigua
expresion
    : primary #PrimitivoExpression
    | ID                               # ReferenceExpression
    | llamadas_funciones # callFunction // no implementar
    | '(' expresion ')'                        # GroupedExpression
    | '-' expresion                            # NegacionExpression
    | expresion op=('*' | '/') expresion               # AritmeticaExpression
    | expresion op=('+' | '-') expresion               # AritmeticaExpression
    | expresion op=('<'|'<='|'>='|'>'|'=='|'!=') expresion # RelacionalExpresion
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
