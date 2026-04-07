# Semana 8 - Ejemplo 1

## Descripción General

En esta semana el compilador evoluciona de un backend aritmético básico a un **compilador con soporte completo de variables, sistema de tipos, ámbitos léxicos y estructuras de control**. Se implementan declaraciones y asignaciones de variables con direccionamiento relativo al frame pointer, un sistema de verificación de tipos en tiempo de compilación (int y bool), manejo de ámbitos (scopes) mediante la clase `Environment`, y las sentencias `if`/`else`, `while`, `break` y `continue` con lowering a etiquetas y saltos condicionales ARM64.

El hito conceptual es transformar el compilador de un emisor de expresiones aritméticas a un **generador de código completo** capaz de manejar estado mutable (variables), decisiones (condicionales) y repetición (ciclos) usando únicamente registros, memoria de pila y saltos.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

No hubo cambios en Grammar.g4 respecto a la semana 7. La sintaxis aceptada y la precedencia permanecen iguales.

- **Sin nueva regla**
```antlr
stmt
    : 'print' '(' e ')'                    
    | 'var' ID '=' e                       
    | ID '=' e                             
    | 'if' '(' e ')' block else?           
    | 'while' '(' e ')' block              
    | 'continue'                           
    | 'break'                              
    | 'return' e?                          
    | 'func' ID '(' params? ')' block      
    | ID '(' args? ')'                     
    | ID ('[' index+=e ']')+ '=' assign=e  
    ;
```
- Semánticamente, la gramática ya soportaba variables, condicionales, ciclos y control de flujo. En semana 7 solo se implementó el subconjunto aritmético + `print`; en semana 8 se implementa el backend para el resto de construcciones.

- **Sin cambios en precedencia/expresiones**
```antlr
e    : eq ;
eq   : left=ineq ('==' right=ineq)? ;
ineq : left=add (op=('>'|'<') right=add)? ;
add  : add op=('+' | '-') prod | prod ;
prod : prod op=('*' | '/') unary | unary ;
unary: primary | '-' unary ;
```
- Las expresiones de comparación (`==`, `>`, `<`) ahora generan instrucciones `cmp` + `cset` en vez de ser ignoradas.

- **Sin nuevos tokens**
```antlr
INT : [0-9]+ ;
ID  : [a-zA-Z_][a-zA-Z0-9_]* ;
WS  : [ \t\r\n]+ -> skip ;
```
- No se incorporan categorías léxicas nuevas; los cambios son puramente en la fase de generación de código.

### Nuevas clases

No se agregaron nuevas clases en semana 8. El conjunto de archivos en `src/` permanece idéntico al de semana 7.

### Clases modificadas

#### `ARM/ASMGenerator.php`

- Qué se agregó
  - 5 nuevos métodos de emisión de instrucciones:
    - `cmp($rs1, $rs2)`: emite instrucción `cmp` para comparar dos registros.
    - `cset($rd, $cond)`: emite instrucción `cset` para establecer un registro según una condición (`eq`, `gt`, `lt`).
    - `label($name)`: emite una etiqueta (ej. `L0:`) para ser destino de saltos.
    - `b($label)`: emite salto incondicional a una etiqueta.
    - `cbz($rs, $label)`: emite salto condicional (branch if zero) a una etiqueta.
- Qué se cambió
  - `toString()`: se agregó detección de etiquetas mediante regex (`/^[A-Za-z_]\w*:$/`) para emitirlas sin indentación, distinguiéndolas de instrucciones regulares que llevan 4 espacios de indentación.
- Por qué fue necesario
  - Las expresiones de comparación requieren `cmp` + `cset` para producir valores booleanos (0 o 1).
  - Las estructuras `if`/`else` y `while` necesitan etiquetas como destinos de salto y las instrucciones `b`/`cbz` para implementar el flujo de control.
  - Sin la detección de etiquetas en `toString()`, las etiquetas se emitirían con indentación y el ensamblador las rechazaría.
- Cómo afecta la ejecución
  - El ensamblador generado ahora contiene etiquetas (`L0:`, `L1:`, ...) y saltos (`b L0`, `cbz x9, L1`) que implementan el flujo de control del programa fuente.

#### `Compiler.php`

- Qué se agregó
  - **4 nuevas propiedades**:
    - `$env`: instancia de `Environment` para el ámbito léxico actual.
    - `$stackOffset`: offset acumulado relativo al frame pointer para asignar slots de variables.
    - `$labelCounter`: contador entero para generar etiquetas únicas (`L0`, `L1`, ...).
    - `$loopLabels`: pila (array) de pares `["start" => ..., "end" => ...]` para resolver `break`/`continue`.
  - **1 método helper**:
    - `newLabel()`: retorna una etiqueta única incrementando `$labelCounter`.
  - **10 nuevos métodos visitor**:
    - `visitVarDeclaration`: evalúa inicializador, asigna slot de 8 bytes (offset -= 8), almacena metadatos `["type", "offset"]` en el entorno, emite `pop` + `subi SP` + `str [FP, offset]`.
    - `visitReferenceExpression`: busca variable en el entorno (con cadena de padres), emite `ldr [FP, offset]` + `push`, retorna el tipo declarado.
    - `visitAssignmentStatement`: evalúa expresión, valida que el tipo coincida con la declaración, emite `pop` + `str [FP, offset]`.
    - `visitBlockStatement`: crea ámbito hijo (`new Environment($prevEnv)`), itera sentencias propagando `FlowType`, restaura ámbito y offset al salir, reclama espacio de pila con `addi SP`.
    - `visitIfStatement`: evalúa condición (valida int/bool), emite `cbz` a etiqueta else/end, visita bloque if, opcionalmente visita else con salto incondicional entre ambos.
    - `visitWhileStatement`: emite etiqueta de inicio, evalúa condición, `cbz` a fin, visita cuerpo, `b` de regreso al inicio, emite etiqueta de fin. Maneja la pila `$loopLabels`.
    - `visitContinueStatement`: emite `b` a la etiqueta de inicio del ciclo actual, retorna `ContinueType()`.
    - `visitBreakStatement`: emite `b` a la etiqueta de fin del ciclo actual, retorna `BreakType()`.
    - `visitEqualityExpression`: evalúa ambos operandos, valida tipos iguales, emite `cmp` + `cset eq`, retorna `"bool"`.
    - `visitInequalityExpression`: evalúa ambos operandos, valida ambos `"int"`, emite `cmp` + `cset gt/lt`, retorna `"bool"`.
    - `visitBoolExpression`: emite `mov T0, #1` o `mov T0, #0` según `true`/`false`, push, retorna `"bool"`.
- Qué se cambió
  - `visitProgram`: ahora emite `mov x29, sp` al inicio para configurar el frame pointer.
  - `visitPrintStatement`: ahora captura el tipo retornado por la expresión (mantiene la cadena de tipos).
  - `visitAddExpression`: ahora valida que ambos operandos sean `"int"` antes de operar, retorna `"int"`.
  - `visitProductExpression`: ahora valida que ambos operandos sean `"int"` antes de operar, retorna `"int"`.
  - `visitUnaryExpression`: ahora valida que el operando sea `"int"`, retorna `"int"`.
  - `visitIntExpression`: ahora retorna `"int"`.
  - `visitGroupedExpression`: ahora propaga el tipo retornado por la expresión interna.
  - `visitPrimaryExpression`: ahora propaga el tipo retornado.
- Por qué fue necesario
  - Para soportar variables se necesita un mecanismo de almacenamiento (frame pointer + offsets) y un entorno de símbolos (`Environment`).
  - Para garantizar programas válidos se necesita verificación de tipos en tiempo de compilación.
  - Para implementar control de flujo se necesitan etiquetas, saltos y propagación de `FlowType`.
- Cómo afecta la ejecución
  - El compilador ahora genera código ARM64 completo para programas con variables, condicionales y ciclos, no solo expresiones aritméticas.

#### `Environment.php` (activada)

- Qué se agregó
  - No se modificó el código.
- Qué se cambió
  - Pasa de estar inactiva (presente pero sin uso en semana 7) a ser **eje central** del manejo de variables.
- Por qué fue necesario
  - Las variables necesitan un ámbito léxico con búsqueda en cadena de padres para soportar bloques anidados.
- Cómo afecta la ejecución
  - Cada bloque (`if`, `while`) crea un ámbito hijo. Las variables se buscan recorriendo la cadena de padres. Los metadatos almacenados (`type`, `offset`) permiten generar direccionamiento correcto y validar tipos.

#### `FlowTypes.php` (activada)

- Qué se agregó
  - No se modificó el código.
- Qué se cambió
  - Pasa de estar inactiva a ser utilizada por `visitBreakStatement`, `visitContinueStatement` y `visitBlockStatement`.
- Por qué fue necesario
  - Las sentencias `break` y `continue` deben detener la generación de código muerto dentro de un bloque. Al retornar un `FlowType`, `visitBlockStatement` deja de iterar las sentencias restantes.
- Cómo afecta la ejecución
  - Evita emitir instrucciones inalcanzables después de un `break` o `continue`, produciendo código ensamblador más limpio y correcto.

### Cambios en el compilador

- Nuevos métodos visitor
  - Se introducen 10 nuevos visitors en `Compiler`:
  - **Variables**: `visitVarDeclaration`, `visitReferenceExpression`, `visitAssignmentStatement`.
  - **Ámbitos**: `visitBlockStatement`.
  - **Control de flujo**: `visitIfStatement`, `visitWhileStatement`, `visitContinueStatement`, `visitBreakStatement`.
  - **Comparaciones**: `visitEqualityExpression`, `visitInequalityExpression`.
  - **Literales**: `visitBoolExpression`.
- Cambios en evaluación
  - Todos los visitors de expresión ahora retornan un **string de tipo** (`"int"` o `"bool"`) que se propaga hacia arriba por el árbol.
  - Las expresiones de comparación (`==`, `>`, `<`) generan instrucciones `cmp` + `cset` y retornan `"bool"`.
  - Los literales booleanos (`true`/`false`) se representan como enteros 1/0 en ARM64.
- Manejo de flujo
  - `if`/`else` se lowerea a secuencias `cbz` + etiquetas + salto incondicional.
  - `while` se lowerea a un ciclo con etiqueta de inicio, `cbz` de salida, y `b` de regreso.
  - `break` emite salto a la etiqueta de fin del ciclo y retorna `BreakType()`.
  - `continue` emite salto a la etiqueta de inicio del ciclo y retorna `ContinueType()`.
  - `visitBlockStatement` propaga `FlowType` para evitar código muerto después de `break`/`continue`.
- Nuevos tipos de retorno
  - Los visitors de expresión retornan `"int"` o `"bool"` (antes no retornaban nada).
  - `visitBreakStatement` retorna `BreakType()`, `visitContinueStatement` retorna `ContinueType()`.
- Cambios en el entorno
  - El modelo de `Environment` es ahora **eje central** del compilador para manejar variables y ámbitos.
  - Cada bloque crea un `new Environment($prevEnv)` y lo restaura al salir.
  - El offset de pila (`$stackOffset`) se guarda y restaura junto con el entorno para reclamar espacio de variables locales.
- Nuevas validaciones semánticas
  - **Tipo de operandos aritméticos**: `+`, `-`, `*`, `/` requieren ambos operandos `"int"`.
  - **Tipo de operandos de comparación**: `>`, `<` requieren ambos `"int"`; `==` requiere tipos iguales.
  - **Tipo de operando unario**: `-` requiere operando `"int"`.
  - **Tipo de condiciones**: `if` y `while` requieren condición `"int"` o `"bool"`.
  - **Tipo de asignación**: el tipo de la expresión debe coincidir con el tipo declarado de la variable.
  - **Contexto de break/continue**: se valida que estén dentro de un ciclo `while`.
  - Todas las validaciones son en tiempo de compilación y lanzan `Exception` de PHP con mensaje descriptivo.

---

## Estructura del Proyecto

- Grammar.g4
  - Define la gramática del lenguaje (sin cambios respecto a semana 7).
- `src/`
  - Contiene el backend de compilación: Compiler.php, `ARM/ASMGenerator.php`, `ARM/Constants.php`, y utilidades de entorno y flujo.
- `src/Environment.php`
  - Gestión de ámbitos léxicos con cadena de padres. Ahora activamente utilizada por el compilador.
- `src/FlowTypes.php`
  - Clases `FlowType`, `ContinueType`, `BreakType`, `ReturnType`. Ahora activamente utilizadas para prevenir código muerto.
- `bootstrap.php`
  - Carga clases ANTLR y archivos del compilador.
- index.php
  - Punto de entrada web; parsea entrada y muestra ensamblador generado.
- `static/`
  - Recursos de interfaz para editor y consola web.
- `ANTLRv4/`
  - Artefactos PHP generados por ANTLR (lexer/parser/visitor/base).
- `vendor/`
  - Dependencias instaladas por Composer (runtime ANTLR4/autoload).

---

## Cómo generar el parser

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

- Qué hace el comando
  - Toma Grammar.g4 y genera el lexer/parser/visitor para **PHP** en `ANTLRv4/`.
- Por qué `-visitor` es requerido
  - El proyecto implementa la semántica mediante visitors (`Compiler extends GrammarBaseVisitor`).
- Qué archivos se generan conceptualmente
  - **Léxico**: `GrammarLexer`.
  - **Sintáctico**: `GrammarParser`.
  - **Recorrido semántico**: `GrammarVisitor` y `GrammarBaseVisitor`.
  - **Metadatos**: archivos `.tokens` y `.interp`.

## Cómo ejecutar el proyecto

```bash
php -S 0.0.0.0:8080
```

- Qué hace
  - Levanta el servidor embebido de PHP en el puerto `8080`.
- Qué debe esperar el estudiante
  - Ingresar un programa en el editor web y obtener como salida el **texto ensamblador ARM64** generado.
  - Programas con variables, condicionales y ciclos ahora generan código con etiquetas y saltos.
- Cómo interactúa con el compilador
  - index.php parsea con ANTLR, invoca `Compiler->visit(tree)`, y renderiza `ASMGenerator->toString()` en la consola.

## Probar código assembly para ARM64

Descargar emulador QEMU para ARM64:

```bash
sudo apt update
sudo apt install -y binutils-aarch64-linux-gnu qemu-user gdb-multiarch build-essential
```

Generar build con bash:

```bash
chmod +x build.sh
./build.sh
```

## Conceptos aprendidos en esta semana

- **Gestión de variables en compilador**: asignación de slots en la pila con offsets relativos al frame pointer (`mov x29, sp`).
- **Ámbitos léxicos (scoping)**: creación de entornos hijo por bloque y restauración al salir, con reclamación de espacio de pila.
- **Sistema de tipos en tiempo de compilación**: cada expresión retorna un tipo (`"int"` o `"bool"`) que se valida en operaciones y asignaciones.
- **Lowering de condicionales**: traducción de `if`/`else` a secuencias de `cbz` (branch if zero), etiquetas y saltos incondicionales.
- **Lowering de ciclos**: traducción de `while` a un patrón etiqueta-inicio → condición → `cbz` fin → cuerpo → `b` inicio → etiqueta-fin.
- **Break y continue**: emisión de saltos directos a etiquetas de ciclo, con propagación de `FlowType` para evitar código muerto.
- **Instrucciones ARM64 de comparación**: uso de `cmp` + `cset` para materializar resultados booleanos en registros.
- **Generación de etiquetas únicas**: contador incremental para evitar colisiones entre etiquetas de distintas estructuras.
- **Direccionamiento FP-relativo**: separación entre la pila de expresiones (SP) y el almacenamiento de variables (offsets fijos desde FP).
