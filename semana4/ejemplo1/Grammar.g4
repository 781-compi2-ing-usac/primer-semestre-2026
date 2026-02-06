grammar Grammar;

// Program
p
    : stmt* EOF                            # Program
    ;

// Statements
stmt
    : 'print' '(' e ')'                    # PrintStatement
    | 'var' ID '=' e                       # VarDeclaration
    | ID '=' e                             # AssignmentStatement
    | 'if' '(' e ')' block else?           # IfStatement
    | 'while' '(' e ')' block              # WhileStatement
    | 'continue'                           # ContinueStatement
    | 'break'                              # BreakStatement    
    ;

block
    : '{' stmt* '}'                        # BlockStatement
    ;

else
    : 'else' block
    ;

/*
    * Expressions, precedence levels
    1. Equality: ==
    2. Inequality: >, <
    3. Addition: +, -
    4. Multiplication: *, /
    5. Unary: -
    6. Primary: INT, ID, (e)
*/

e    
    : eq                       
    ;

eq
    : left=ineq ('==' right=ineq)?     # EqualityExpression
    ;

ineq
    : left=add (op=('>'|'<') right=add)? # InequalityExpression    
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
    | bool=('true'|'false')            # BoolExpression
    ;

// Lexer rules
INT : [0-9]+ ;
ID  : [a-zA-Z_][a-zA-Z0-9_]* ;
WS  : [ \t\r\n]+ -> skip ;
