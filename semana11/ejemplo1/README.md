# Semana 11 - Ejemplo 1

## Descripción General

En este incremento el compilador mantiene el soporte de arrays/heap de la entrega anterior y agrega el bloque de **llamadas a funciones** con generación ARM64 real:

- Llamadas a funciones nativas (ej. `time()`) con ABI AAPCS64.
- Declaración y llamada de funciones de usuario (`func ...`) como funciones "foreign" compiladas a labels ARM64.
- Soporte de `return` en funciones.
- Retorno por defecto `0` cuando una función no ejecuta `return` explícito.
- Soporte de recursión directa.
- Validaciones semánticas de aridad y de símbolo invocable.

El hito conceptual de esta semana es pasar de evaluar expresiones/arrays a **ejecutar llamadas de función con frame propio**, preservando convención de llamada y compatibilidad con el entorno léxico.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

No hubo cambios en `Grammar.g4`. Las reglas de funciones ya existían y en esta entrega se implementó su semántica en el compilador.

- **Sin nueva regla (se implementa semántica sobre reglas existentes)**
```antlr
stmt
    : 'print' '(' e ')'                    # PrintStatement
    | 'var' ID '=' e                       # VarDeclaration
    | ID '=' e                             # AssignmentStatement
    | 'if' '(' e ')' block else?           # IfStatement
    | 'while' '(' e ')' block              # WhileStatement
    | 'continue'                           # ContinueStatement
    | 'break'                              # BreakStatement
    | 'return' e?                          # ReturnStatement
    | 'func' ID '(' params? ')' block      # FunctionDeclaration
    | ID '(' args? ')'                     # FunctionCallStatement
    | ref_list ']' '=' assign=e            # ArrayAssignmentStatement
    ;

primary
    : '(' e ')'                            # GroupedExpression
    | INT                                  # IntExpression
    | ID                                   # ReferenceExpression
    | bool=('true'|'false')                # BoolExpression
    | ID '(' args? ')'                     # FunctionCallExpression
    | array                                # ArrayExpression
    | ref_list ']'                         # ArrayAccessExpression
    ;
```

### Nuevas clases

No se agregaron clases nuevas para esta etapa; se reutilizó la base existente.

### Clases modificadas

#### `src/Compiler.php`

- Qué se agregó
  - **Resolución de funciones en el mismo `Environment`**:
    - Símbolos `native_fn` y `foreign_fn` comparten namespace con variables.
  - **Llamadas con ABI AAPCS64**:
    - Paso de argumentos por `x0..x7`.
    - Retorno por `x0`.
    - Alineación de `SP` a 16 bytes antes de `bl`.
  - **Funciones de usuario (`func`)**:
    - Registro de descriptor de función (label, aridad, params, closure env).
    - Emisión de cuerpo de función en `.text` con prologue/epilogue.
    - Soporte de recursión.
  - **`return` compilado**:
    - `return e` carga resultado en `x0` y salta al epílogo.
    - Si no hay `return`, la función retorna `0`.

- Qué se cambió
  - `visitFunctionCallExpression` y `visitFunctionCallStatement` ahora generan llamada real.
  - `visitProgram` ahora emite, además del main y handlers, los cuerpos de funciones foreign y natives usados.
  - Se mantiene el soporte de arrays sin romper compatibilidad.

- Por qué fue necesario
  - Para que `func`, `return` y llamadas dejaran de ser placeholders y pasaran a ejecución ARM64 real.
  - Para permitir recursión y composición de llamadas (foreign -> foreign, foreign -> native).

- Cómo afecta la ejecución
  - El programa compilado puede declarar funciones, llamarlas y retornar valores correctamente.
  - Las llamadas respetan convención de llamada y stack frame.

#### `src/ARM/ASMGenerator.php`

- Qué se agregó
  - Helpers de soporte ABI/funciones:
    - `ret()`
    - `stpPre(...)`
    - `ldpPost(...)`
    - `andi(...)`
  - Emisor nativo:
    - `emitNativeTime($label)`

- Qué se cambió
  - Sección `.bss` extendida con:
    - `native_time_spec: .skip 16`

- Por qué fue necesario
  - Para generar prologue/epilogue AAPCS64 y código de función nativa (`time`).

- Cómo afecta la ejecución
  - El ensamblador generado incluye funciones auxiliares invocables por `bl`.

#### `src/Natives.php`

- Qué se cambió
  - Pasó de objetos de intérprete a **tabla de descriptores de compilación**.

- Cómo se reutilizó
  - Sigue siendo el punto central para definir funciones nativas disponibles.

#### `bootstrap.php`

- Qué se cambió
  - Se mantiene carga explícita de clases, incluyendo `src/Natives.php`.

#### `src/Environment.php` (reutilizada)

- Qué se cambió
  - No se modificó código.

- Cómo se reutilizó
  - Ahora guarda variables y funciones en el mismo entorno, manteniendo simplicidad del modelo.

### Cambios en el compilador

- Convención de llamada AAPCS64 aplicada
  - `x0..x7`: argumentos.
  - `x0`: retorno.
  - `SP` alineado a 16 bytes en sitio de llamada.
  - Prologue/epilogue estándar en funciones foreign/nativas.

- Modelo de funciones
  - Native:
    - Descriptor + label + emisor en ASMGenerator.
  - Foreign:
    - Descriptor compilado desde `func`.
    - Cuerpo emitido al final del programa.
    - Retorno implícito `0` si no hay `return`.

- Validaciones semánticas nuevas
  - Función no definida.
  - Símbolo no invocable.
  - Aridad incorrecta.
  - Límite de 8 argumentos/parámetros en esta etapa.

---

## Estructura del Proyecto

- `Grammar.g4`
  - Gramática del lenguaje (sin cambios en esta etapa).
- `src/Compiler.php`
  - Semántica y generación ARM64 (arrays + llamadas native/foreign).
- `src/Natives.php`
  - Registro de descriptores de funciones nativas.
- `src/ARM/ASMGenerator.php`
  - Emisión de instrucciones, helpers ABI y código nativo.
- `src/ARM/Constants.php`
  - Convenciones de registros.
- `src/Environment.php`
  - Tabla de símbolos por scopes encadenados.
- `bootstrap.php`
  - Carga runtime ANTLR + clases del compilador.
- `index.php`
  - Entrada web para parsear y mostrar ensamblador.
- `build.sh`
  - Ensamblado + link + ejecución con QEMU ARM64.

---

## Cómo generar el parser

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

## Cómo ejecutar el proyecto

```bash
php -S 0.0.0.0:8080
```

## Probar código assembly para ARM64

Instalar herramientas:

```bash
sudo apt update
sudo apt install -y binutils-aarch64-linux-gnu qemu-user gdb-multiarch build-essential
```

Compilar y ejecutar `main.asm`:

```bash
chmod +x build.sh
./build.sh
```

---

## Ejemplos de entrada

Native call:

```txt
print(time())
```

Foreign call:

```txt
func id(a){
  return a
}
print(id(5))
```

Recursión:

```txt
func fact(n){
  if (n < 2) { return 1 }
  return n * fact(n - 1)
}
print(fact(5))
```

Sin return explícito:

```txt
func f(){
  var x = 1
}
print(f())   // 0
```

Errores esperados:

```txt
print(foo())    // función no definida
print(time(1))  // aridad incorrecta
```

---

## Conceptos aprendidos en esta semana

- **AAPCS64 en compiladores**: paso de argumentos, retorno y alineación de stack.
- **Stack frame de función**: prologue/epilogue y retorno estructurado.
- **Funciones nativas vs foreign**: descriptores, labels y estrategia de emisión.
- **Recursión compilada**: uso correcto de `bl` y frames anidados.
- **Modelo de entorno unificado**: variables y funciones en el mismo `Environment`.
