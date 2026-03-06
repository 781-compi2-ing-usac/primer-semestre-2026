# Semana 7 - Ejemplo 1

## Descripción General

En esta semana el proyecto da un salto de **intérprete evaluador** a **compilador fuente-a-texto**: en lugar de ejecutar directamente el programa sobre un entorno de variables, ahora se recorre el árbol sintáctico para generar **código ensamblador AArch64 (ARM64)**.

El hito conceptual es introducir una fase de **generación de código** usando el mismo **visitor pattern** de ANTLR4, modelando la evaluación de expresiones con una pila explícita (`push/pop`) y emisión de instrucciones.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

No hubo cambios en Grammar.g4 respecto a la semana 6. La sintaxis aceptada y la precedencia permanecen iguales.

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
- Semánticamente, esto habilita el **mismo lenguaje fuente**, pero en semana 7 cambia el backend (de evaluación directa a emisión de ensamblador).

- **Sin cambios en precedencia/expresiones**
```antlr
e    : eq ;
eq   : left=ineq ('==' right=ineq)? ;
ineq : left=add (op=('>'|'<') right=add)? ;
add  : add op=('+' | '-') prod | prod ;
prod : prod op=('*' | '/') unary | unary ;
unary: primary | '-' unary ;
```
- Semánticamente, se conserva el orden de evaluación de expresiones aritméticas; lo nuevo ocurre en cómo se materializa el resultado (instrucciones ARM en vez de valor PHP inmediato).

- **Sin nuevos tokens**
```antlr
INT : [0-9]+ ;
ID  : [a-zA-Z_][a-zA-Z0-9_]* ;
WS  : [ \t\r\n]+ -> skip ;
```
- No se incorporan categorías léxicas nuevas; el cambio es puramente en la fase de ejecución/generación.

### Nuevas clases

#### Compiler.php

- Responsabilidad principal
  - Implementar un **visitor compilador** (`extends GrammarBaseVisitor`) que traduce nodos del parse tree a instrucciones ARM.
- Atributos importantes
  - `$code`: instancia de `ASMGenerator` donde se acumulan instrucciones.
  - `$r`: mapa de registros cargado desde `ARM/Constants.php`.
- Métodos clave
  - `visitProgram`: recorre sentencias y finaliza con `endProgram()`.
  - `visitPrintStatement`: evalúa expresión, extrae de pila y emite rutina de impresión.
  - `visitAddExpression`, `visitProductExpression`, `visitUnaryExpression`, `visitIntExpression`, `visitGroupedExpression`, `visitPrimaryExpression`.
- Rol dentro del intérprete
  - Reemplaza al evaluador directo como **motor semántico principal** del pipeline.
- Relación con clases existentes
  - Usa clases ANTLR generadas (`GrammarBaseVisitor`, contextos) y delega emisión a `ASMGenerator`.

#### `ARM/ASMGenerator.php`

- Responsabilidad principal
  - Actuar como **backend de generación de ensamblador** (instrucciones, utilidades de pila, syscalls, serialización final).
- Atributos importantes
  - `instr`: buffer de instrucciones.
  - `r`: registros ARM cargados de Constants.php.
- Métodos clave
  - Emisión aritmética: `add`, `sub`, `mul`, `div`.
  - Memoria/pila: `str`, `ldr`, `push`, `pop`.
  - Flujo de salida: `printInt`, `printNewLine`, `endProgram`.
  - Serialización: `toString`, además de rutina auxiliar `itoa`.
- Rol dentro del intérprete
  - Encapsula la **representación intermedia concreta** (texto assembly) y su formateo.
- Relación con clases existentes
  - Es consumida por Compiler.php; no depende del entorno léxico de `Environment.php`.

#### `ARM/Constants.php`

- Responsabilidad principal
  - Definir el **mapeo simbólico de registros ARM64** (`A0`, `T0`, `SP`, etc.).
- Atributos importantes
  - Arreglo asociativo de alias de registros.
- Métodos clave
  - No aplica (archivo de constantes).
- Rol dentro del intérprete
  - Proveer nombres estables para emitir instrucciones legibles y consistentes.
- Relación con clases existentes
  - Es cargado por Compiler.php y ASMGenerator.php.

### Clases modificadas

No se modificaron clases existentes dentro de `src/` que ya estaban en semana 6 (`Environment.php`, `FlowTypes.php`, `Foreigns.php`, `Invocable.php`, `Natives.php`).

#### Interpreter.php (retirada)

- Qué se agregó
  - No aplica.
- Qué se cambió
  - El archivo deja de existir en semana 7.
- Por qué fue necesario
  - Se sustituyó la estrategia de ejecución directa por compilación a ensamblador.
- Cómo afecta la ejecución del intérprete
  - Ya no se evalúan variables/funciones/arreglos en runtime PHP; se genera código ARM como salida.

### Cambios en el intérprete

- Nuevos métodos visitor
  - Se introducen visitors orientados a codegen en `Compiler`:
  - `visitProgram`, `visitPrintStatement`, `visitAddExpression`, `visitProductExpression`, `visitUnaryExpression`, `visitIntExpression`, `visitGroupedExpression`, `visitPrimaryExpression`.
- Cambios en evaluación
  - La semántica pasa de **evaluación inmediata** a **emisión de instrucciones** con pila.
  - Expresiones numéricas generan secuencias `mov`/`push`/`pop` + operación aritmética.
- Manejo de flujo
  - En el compilador actual no hay lowering explícito de `if`, `while`, `break`, `continue`, `return` ni funciones/arreglos.
  - Aunque la gramática los parsea, el backend implementado se centra en el subconjunto aritmético + `print`.
- Nuevos tipos de retorno
  - `visitProgram` retorna un objeto `ASMGenerator` (no un `string` de consola).
  - index.php ahora llama `toString()` para obtener el ensamblador final.
- Cambios en el entorno
  - El modelo de `Environment` deja de ser eje de ejecución en semana 7.
- Nuevas validaciones semánticas
  - Se mantiene validación sintáctica por parser ANTLR.
  - En backend se agrega validación de operador conocido (`+`, `-`, `*`, `/`) con `Exception` en caso contrario.

---

## Estructura del Proyecto

- Grammar.g4
  - Define la gramática del lenguaje (sin cambios respecto a semana 6).
- `src/`
  - Contiene el backend de compilación: Compiler.php, `ARM/ASMGenerator.php`, `ARM/Constants.php`, y utilidades heredadas.
- `bootstrap.php`
  - Carga clases ANTLR y ahora también Compiler.php + archivos ARM.
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
- Cómo interactúa con el intérprete/compilador
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

- **Transición de intérprete a compilador**.
- **Generación de código** dirigida por árbol sintáctico.
- **Visitor pattern** aplicado a backend.
- **Modelo de evaluación por pila** (`push/pop`) para expresiones.
- **Mapeo de operaciones de alto nivel a instrucciones ARM64**.
- **Separación frontend/backend** en un compilador simple:
  - frontend: lexer/parser ANTLR
  - backend: emisor de ensamblador
- **Representación textual de programa objeto** (assembly) como artefacto de compilación.