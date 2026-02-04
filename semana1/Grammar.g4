grammar Grammar;

l : e
  ;

e : e '+' t     # Add
  | t           # Et
  ;

t : t '*' f     #Product
  | f           #Tf
  ;

f : '(' e ')'   #Paren
  | DIGIT       #Int
  ;

DIGIT : [0-9]+;
WS : [ \t\r\n]+ -> skip;