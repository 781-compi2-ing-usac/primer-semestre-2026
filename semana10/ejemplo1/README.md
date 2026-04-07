# Semana 10 - Ejemplo 1

## Descripción General

En esta semana el compilador agrega el bloque de memoria dinámica del lenguaje: **heap pointer + arrays + accesos multiíndice en row-major order**. Se mantiene la arquitectura de semana anterior (entorno léxico, tipos, control de flujo, stack con `FP`) y se extiende con:

- Inicialización de heap en tiempo de ejecución (`heap_base`, `heap_end`, `HP`).
- Reserva de memoria por *bump allocator* (sin `free`, sin GC).
- Literales de arrays en heap con header `rank + dims + data`.
- Acceso y asignación por índices (`arr[i]`, `m[i][j]`, `t[i][j][k]`) con cálculo row-major.
- Validaciones semánticas (tipos, rectangularidad, cantidad de índices).
- Validaciones runtime (out-of-bounds y out-of-memory).

El hito conceptual es pasar de variables escalares en stack a **estructuras indexables en heap**, manteniendo tipado estático y generación ARM64 directa.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

No hubo cambios en `Grammar.g4` para esta implementación. Se reutilizó la sintaxis ya existente para arrays.

- **Sin nueva regla (se reutiliza lo ya definido)**
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
    | ID ('[' index+=e ']')+ '=' assign=e  # ArrayAssignmentStatement
    ;

primary
    : '(' e ')'                        # GroupedExpression
    | INT                              # IntExpression
    | ID                               # ReferenceExpression
    | bool=('true'|'false')            # BoolExpression
    | ID '(' args? ')'                 # FunctionCallExpression
    | '[' e (',' e)* ']'               # ArrayExpression
    | ID ('[' e ']')+                  # ArrayAccessExpression
    ;
```

### Nuevas clases

No se agregaron nuevas clases. Se reutilizó la estructura existente del proyecto.

### Clases modificadas

#### `src/Compiler.php`

- Qué se agregó
  - **Soporte de tipos estructurados para arrays**:
    - Descriptor: `['kind'=>'array','elem'=>..., 'rank'=>N, 'dims'=>[...]]`.
  - **Helpers de tipos**:
    - `typeToString`, `typeEquals`, `isArrayType`, `makeArrayTypeWithRankAndDims`, etc.
  - **Helpers de heap/runtime**:
    - `emitHeapAllocFixed` para reservar memoria dinámica con verificación de límite.
    - `emitRuntimeErrorHandlers` con labels `_panic_oob` y `_panic_oom`.
  - **Helpers de arrays**:
    - Análisis de literal multidimensional y validación de rectangularidad.
    - Cálculo de dirección row-major para `rank` arbitrario.
  - **Visitors implementados/completados**:
    - `visitArrayExpression`
    - `visitArrayAccessExpression`
    - `visitArrayAssignmentStatement`

- Qué se cambió
  - `visitProgram` ahora inicializa heap:
    - `ldr HP, =heap_base`
    - `ldr HEAP_END, =heap_end`
  - Se endureció validación de tipos en asignación/comparaciones para soportar arrays estructurados.
  - `print` restringido a valores escalares (`int`/`bool`).

- Por qué fue necesario
  - Los arrays requieren almacenamiento fuera del stack de variables locales.
  - Multiíndice exige conocer dimensiones y aplicar fórmula row-major.
  - Sin checks runtime, accesos inválidos terminan en memoria corrupta.

- Cómo afecta la ejecución
  - El compilador emite ARM64 que reserva, indexa y escribe arrays en heap.
  - Los errores OOB/OOM terminan la ejecución con códigos de salida controlados.

#### `src/ARM/ASMGenerator.php`

- Qué se agregó
  - `bcond($cond, $label)` para emitir `b.<cond>` (ej. `b.ge`, `b.lt`, `b.hi`).

- Qué se cambió
  - Sección `.bss` extendida con heap:
    - `heap_base: .skip 1048576`
    - `heap_end:`

- Por qué fue necesario
  - Comparaciones de rango y capacidad de heap requieren saltos condicionales más generales.
  - El compilador necesita una región concreta de heap para reservar arrays.

- Cómo afecta la ejecución
  - El ensamblador generado ahora contiene memoria dinámica explícita para arrays.

#### `src/ARM/Constants.php`

- Qué se agregó
  - Alias semánticos:
    - `HP => x20`
    - `HEAP_END => x21`

- Por qué fue necesario
  - Documenta y estabiliza el convenio de registros para heap pointer y límite.

#### `src/Environment.php` (reutilizada)

- Qué se cambió
  - No se modificó código.
- Cómo se reutilizó
  - Se sigue usando para guardar símbolos (`type`, `offset`), ahora con tipos de array estructurados.

### Cambios en el compilador

- Modelo de memoria
  - Stack: variables locales (slots con offset relativo a `FP`).
  - Heap: arrays (bump allocator, sin `free`).

- Layout de array en heap
  - `base + 0`: `rank`
  - `base + 8 ...`: dimensiones (`dims[0]`, `dims[1]`, ...)
  - `base + (rank+1)*8`: datos lineales en row-major

- Cálculo de offset row-major
  - Para índices `i0..ik` y dimensiones `d0..dk`:
  - `linear = (((i0 * d1 + i1) * d2 + i2) ... )`
  - `addr = base + headerBytes + linear * 8`

- Validaciones semánticas nuevas
  - Literales de array no vacíos.
  - Literales multidimensionales rectangulares (sin *jagged arrays*).
  - Elementos homogéneos (`int` o `bool`, o base escalar consistente).
  - Cantidad de índices debe coincidir con `rank`.
  - Cada índice debe ser `int`.
  - En asignación indexada, RHS debe coincidir con tipo escalar base del array.

- Validaciones runtime nuevas
  - Bounds check por dimensión (`0 <= idx < dim`).
  - Heap overflow check antes de reservar memoria.

---

## Estructura del Proyecto

- `Grammar.g4`
  - Gramática del lenguaje (sin cambios esta semana).
- `src/Compiler.php`
  - Semántica y generación ARM64 (tipos, heap, arrays, row-major).
- `src/ARM/ASMGenerator.php`
  - Emisión de instrucciones y secciones ASM (`.text`, `.bss`, `.rodata`).
- `src/ARM/Constants.php`
  - Convenciones de registros (`FP`, `SP`, temporales, `HP`, `HEAP_END`).
- `src/Environment.php`
  - Tabla de símbolos por scopes encadenados.
- `bootstrap.php`
  - Carga runtime ANTLR + clases del compilador.
- `index.php`
  - Entrada web para parsear y mostrar el ensamblador generado.
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

Array 1D:

```txt
var a = [10,20,30]
a[1] = 99
print(a[1])
```

Array 2D (row-major):

```txt
var m = [[1,2,3],[4,5,6]]
print(m[1][2])
```

Array 3D:

```txt
var t = [[[1,2]],[[3,4]]]
print(t[1][0][1])
```

Errores esperados:

```txt
var m = [[1,2],[3]]      // no rectangular
print(m[1])              // faltan índices para rank=2
print(m[2][0])           // out of bounds (runtime)
```

---

## Conceptos aprendidos en esta semana

- **Heap pointer en compiladores**: inicialización y avance monotónico (`bump allocation`).
- **Representación interna de arrays**: `rank + dims + data` para acceso multiíndice.
- **Row-major order**: linealización de índices multidimensionales.
- **Chequeos de seguridad en runtime**: límites de índice y capacidad de heap.
- **Reutilización de infraestructura previa**: `Environment` para metadatos sin tocar gramática.
- **Integración incremental**: 1D estable, luego extensión multiíndice manteniendo compatibilidad.
