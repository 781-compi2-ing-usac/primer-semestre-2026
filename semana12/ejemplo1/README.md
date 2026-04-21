# Semana 12 - Ejemplo 1

## Descripción General

En este incremento el compilador mantiene el soporte de funciones/arrays de la entrega anterior y agrega el bloque de **paso de arrays por referencia** para funciones foreign, sin cambiar la gramática del lenguaje.

- Paso por referencia automático cuando un parámetro se usa como array dentro de la función (`a[i]`, `len(a)` o `a = [...]`).
- Paso por valor para parámetros escalares (`int`, `bool`) como en la etapa anterior.
- Propagación al caller de:
  - escritura de elementos (`a[i] = v`)
  - reasignación completa del parámetro (`a = [...]`)
- Restricción semántica para by-ref: el argumento debe ser variable (l-value), no literal ni expresión compleja.
- Nueva función nativa `len(array)` para recuperar el tamaño de la primera dimensión.

El hito conceptual de esta semana es movernos de llamadas con copia de valores a **aliasing controlado de celdas de variables**, manteniendo ABI AAPCS64 y acceso a arrays en heap.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

No hubo cambios en `Grammar.g4`. El comportamiento de referencia se implementó a nivel semántico en compilación.

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

#### `src/FunctionParamAnalyzer.php`

- Qué se agregó
  - Análisis semántico de parámetros de funciones para inferir modo de paso:
    - `by_ref` para parámetros usados como array.
    - `by_value` para parámetros escalares.
  - Producción de metadata por función:
    - `argTypes`
    - `argModes`

- Por qué fue necesario
  - Para habilitar referencia de arrays sin extender la sintaxis con operadores como `&` o `*`.

- Cómo afecta la ejecución
  - Cada función foreign se compila con contrato explícito de tipos y modo de paso por parámetro.

#### `src/SymbolAccessEmitter.php`

- Qué se agregó
  - Operaciones de acceso a símbolos centralizadas:
    - lectura/escritura escalar directa o por referencia,
    - carga de puntero base de array,
    - cálculo de dirección de celda para paso by-ref.

- Por qué fue necesario
  - Para evitar que `Compiler.php` crezca con helpers privados y mantenerlo centrado en `visitContext`.

- Cómo afecta la ejecución
  - Los accesos a variables y arrays respetan aliasing de parámetros by-ref en código ARM64.

### Clases modificadas

#### `src/Compiler.php`

- Qué se agregó
  - Integración con `FunctionParamAnalyzer` y `SymbolAccessEmitter`.
  - Validación de argumentos by-ref:
    - solo se acepta variable (ID) como argumento.
  - Emisión de llamada con carga de dirección de variable para parámetros by-ref.
  - Metadata de parámetros en descriptor foreign (`argTypes`, `argModes`, `byRef`).
  - Comentarios ASM (`$this->code->comment()`) en bloques complejos:
    - preparación de argumentos,
    - alineación de stack,
    - prologue/epilogue,
    - acceso y asignación de arrays,
    - linearización row-major.

- Qué se cambió
  - Lectura/escritura de símbolos y carga de puntero de arrays delegadas a `SymbolAccessEmitter`.
  - Materialización de parámetros foreign ahora distingue `by_value` vs `by_ref`.

- Por qué fue necesario
  - Para soportar semántica de referencia en arrays y mejorar trazabilidad del ASM generado.

- Cómo afecta la ejecución
  - Mutaciones y reasignaciones de parámetros array dentro de funciones se reflejan en el llamador.

#### `src/Natives.php`

- Qué se cambió
  - Se agregó descriptor de función nativa `len`:
    - aridad 1,
    - argumento array rank 1,
    - retorno `int`.

- Cómo afecta la ejecución
  - Permite expresar límites de loops sobre arrays sin cambiar la gramática.

#### `src/ARM/ASMGenerator.php`

- Qué se agregó
  - Emisor nativo `emitNativeLen($label)`.
  - Comentarios de bloques en natives (`time`, `len`) para facilitar lectura del ASM.

- Qué se cambió
  - Natives ahora quedan mejor delimitadas en el ensamblador generado.

- Cómo afecta la ejecución
  - `len(array)` retorna `dim[0]` leyendo header del array en heap.

#### `src/TypeChecker.php`

- Qué se cambió
  - Comparación flexible de tipos array cuando solo uno de los lados tiene `dims` explícitas.

- Por qué fue necesario
  - Para validar firmas de funciones/nativas que esperan `array<int> rank=1` contra arrays concretos con dimensiones conocidas.

- Cómo afecta la ejecución
  - Se mantiene validación estática útil sin bloquear llamadas válidas por metadata parcial.

#### `bootstrap.php`

- Qué se cambió
  - Se agregaron `require_once` para:
    - `src/FunctionParamAnalyzer.php`
    - `src/SymbolAccessEmitter.php`

---

### Cambios en el compilador

- Convención de llamada AAPCS64 aplicada
  - `x0..x7`: argumentos.
  - `x0`: retorno.
  - `SP` alineado a 16 bytes antes de `bl`.

- Modelo de paso de parámetros
  - Escalares: `by_value` (valor en registro).
  - Arrays: `by_ref` (dirección de celda variable del caller).

- Modelo de arrays por referencia
  - `a[i] = v` modifica heap compartido.
  - `a = [...]` reescribe la celda variable del caller (alias real).

- Validaciones semánticas nuevas
  - Parámetro by-ref requiere argumento variable.
  - Error explícito cuando se intenta pasar literal/expresión en by-ref.

---

## Estructura del Proyecto

- `Grammar.g4`
  - Gramática del lenguaje (sin cambios en esta etapa).
- `src/Compiler.php`
  - Visitor principal y generación ARM64.
- `src/FunctionParamAnalyzer.php`
  - Inferencia de `argTypes`/`argModes` para funciones foreign.
- `src/SymbolAccessEmitter.php`
  - Emisión de acceso a símbolos directos y por referencia.
- `src/Natives.php`
  - Registro de descriptores de funciones nativas (`time`, `len`).
- `src/ARM/ASMGenerator.php`
  - Emisión de instrucciones y código nativo ARM64.
- `src/TypeChecker.php`
  - Reglas de equivalencia de tipos escalares/arrays.
- `src/Environment.php`
  - Tabla de símbolos por scopes encadenados.
- `bootstrap.php`
  - Carga runtime ANTLR + clases del compilador.
- `index.php`
  - Entrada web para parsear y mostrar ensamblador.
- `Makefile`
  - Ensamblado, link y ejecución con QEMU ARM64.

---

## Cómo generar el parser

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

## Cómo ejecutar el proyecto

```bash
php -S 0.0.0.0:8080
```

## Probar código assembly para ARM64 (Makefile)

Instalar herramientas:

```bash
sudo apt update
sudo apt install -y binutils-aarch64-linux-gnu qemu-user gdb-multiarch build-essential
```

Compilar y ejecutar:

```bash
make clean
make run
```

---

## Ejemplos de entrada

### Paso por referencia de array

```txt
func touch(a){
  a[0] = 99
}

var arr = [1,2,3]
touch(arr)
print(arr[0])    // 99
```

### Reasignación por referencia

```txt
func replace(a){
  a = [7,8,9]
}

var arr = [1,2,3]
replace(arr)
print(arr[0])    // 7
```

### Uso de `len(array)`

```txt
var arr = [10,20,30,40]
print(len(arr))   // 4
```

### Bubble sort sobre array por referencia

```txt
func bubble_sort(a){
  var n = len(a)
  var i = 0
  while (i < n) {
    var j = 0
    while (j < (n - i - 1)) {
      var left = a[j]
      var right = a[j + 1]
      if (left > right) {
        a[j] = right
        a[j + 1] = left
      }
      j = j + 1
    }
    i = i + 1
  }
}

var arr = [5,1,4,2,8]
bubble_sort(arr)
print(arr[0])
print(arr[1])
print(arr[2])
print(arr[3])
print(arr[4])
```

Salida esperada:

```txt
1
2
4
5
8
```

### Error esperado by-ref

```txt
func f(a){
  a[0] = 1
}

f([3,2,1])
// Error: argumento debe ser una variable para paso por referencia
```

---

## Conceptos aprendidos en esta semana

- **Paso por referencia sin nueva sintaxis**: inferencia semántica en compilación.
- **Aliasing de celdas de variables**: diferencia entre pasar valor y pasar dirección.
- **Arrays en heap + variables en stack**: interacción correcta para lectura/escritura/reasignación.
- **Nativas de utilidad para estructuras**: `len(array)` como soporte de algoritmos.
- **Legibilidad de ASM**: uso de comentarios para bloques críticos de generación.
