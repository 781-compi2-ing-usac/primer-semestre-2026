# Código de 3 Direcciones y su Traducción a AArch64

Basándome en el archivo `c3d-decl-array.py`, te explico cómo funciona el código de 3 direcciones (C3D) y cómo se traduce a ensamblador AArch64.

---

## Tabla de Contenidos

1. [Concepto Clave](#concepto-clave)
2. [Estructuras Generales de C3D](#estructuras-generales-de-c3d)
3. [Ejemplo: Declarar un Array](#ejemplo-declarar-un-array-x--1-2-3)
4. [Resumen Visual](#resumen-visual)
5. [Estructura en Memoria](#estructura-en-memoria)
6. [Instrucciones AArch64 - Explicación Detallada](#instrucciones-aarch64---explicación-detallada)
7. [Registros Utilizados](#registros-utilizados)

---

## Concepto Clave

El código de 3 direcciones es una representación intermedia donde cada instrucción tiene **máximo 3 operandos**:
```
destino = operando1 operador operando2
```

---

## Estructuras Generales de C3D

El código de 3 direcciones utiliza estructuras de memoria simuladas para representar cómo un compilador maneja los datos en tiempo de ejecución.

### 1. HEAP (Montículo)

```python
heap = [None] * 100      # Memoria dinámica
HEAPPOINTER = 0          # Puntero al siguiente espacio libre
```

**Propósito:** Almacenar datos dinámicos como:
- Arrays y sus elementos
- Strings (cadenas de caracteres)
- Objetos y estructuras
- Datos cuyo tamaño se conoce en tiempo de ejecución

**Características:**
- Crece hacia direcciones altas (HEAPPOINTER aumenta)
- Los datos persisten hasta que se liberan explícitamente
- Se accede mediante punteros/referencias

**Ejemplo de uso:**
```python
# Almacenar array [1, 2, 3]
t2 = HEAPPOINTER              # Guardar inicio del array
heap[t2 + 0] = 3              # Longitud
heap[t2 + 1] = 1              # Elemento 0
heap[t2 + 2] = 2              # Elemento 1
heap[t2 + 3] = 3              # Elemento 2
HEAPPOINTER = HEAPPOINTER + 4 # Actualizar puntero
```

### 2. STACK (Pila)

```python
stack = [None] * 100     # Pila de ejecución
STACKPOINTER = 0         # Puntero al tope de la pila
```

**Propósito:** Almacenar:
- Variables locales
- Referencias a datos en el heap
- Parámetros de funciones
- Direcciones de retorno

**Características:**
- Estructura LIFO (Last In, First Out)
- Cada función tiene su propio "frame" en el stack
- Se libera automáticamente al salir de una función

**Ejemplo de uso:**
```python
# Guardar referencia al array
stack[STACKPOINTER] = t2      # t2 = dirección en heap
STACKPOINTER = STACKPOINTER + 1
```

### 3. FRAMEPOINTER (Puntero de Marco)

```python
FRAMEPOINTER = 0         # Referencia al marco actual
```

**Propósito:** 
- Marca el inicio del marco de activación de la función actual
- Permite acceso relativo a variables locales y parámetros
- Facilita la navegación en llamadas anidadas

**Uso en funciones:**
```python
# Al entrar a una función
old_fp = FRAMEPOINTER
FRAMEPOINTER = STACKPOINTER

# Acceder a variable local (offset relativo)
variable = stack[FRAMEPOINTER + offset]

# Al salir de la función
FRAMEPOINTER = old_fp
```

### 4. Temporales (t1, t2, t3, ...)

```python
t1 = STACKPOINTER        # Temporal para guardar posición
t2 = HEAPPOINTER         # Temporal para guardar referencia
t3 = t1 + t2             # Temporal para resultado de operación
```

**Propósito:**
- Almacenar resultados intermedios de operaciones
- Representan registros virtuales ilimitados
- El compilador los asigna a registros reales o memoria

**Regla de 3 direcciones:**
```python
# Expresión compleja: x = (a + b) * (c - d)
# Se descompone en:
t1 = a + b
t2 = c - d
t3 = t1 * t2
x = t3
```

### 5. Etiquetas (Labels)

```python
L1:                      # Etiqueta para saltos
    # código
    goto L2              # Salto incondicional
L2:
    if t1 > 0 goto L1    # Salto condicional
```

**Propósito:**
- Marcar destinos de saltos
- Implementar estructuras de control (if, while, for)
- Puntos de entrada de funciones

### Diagrama de Memoria en AArch64

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     MEMORIA EN AArch64                                  │
│                                                                         │
│  Direcciones ALTAS (0xFFFF...)                                          │
│  ┌─────────────────────────────────────────────────────────────────┐    │
│  │                         STACK                                   │    │
│  │  ┌─────────────┐                                                │    │
│  │  │     ...     │  ← Inicio del stack (dirección alta)           │    │
│  │  ├─────────────┤                                                │    │
│  │  │ ret address │  ← Dirección de retorno                        │    │
│  │  ├─────────────┤                                                │    │
│  │  │ frame ptr   │  ← Frame pointer guardado (x29)                │    │
│  │  ├─────────────┤ ← FP (Frame Pointer)                           │    │
│  │  │ var local 1 │  [FP - 8]                                      │    │
│  │  ├─────────────┤                                                │    │
│  │  │ var local 2 │  [FP - 16]                                     │    │
│  │  ├─────────────┤                                                │    │
│  │  │ ref a heap ─────────────────────────────────┐                │    │
│  │  ├─────────────┤                               │                │    │
│  │  │             │ ← SP (Stack Pointer)          │                │    │
│  │  └─────────────┘                               │                │    │
│  │        ↓                                       │                │    │
│  │    Crece hacia ABAJO                           │                │    │
│  │    (direcciones menores)                       │                │    │
│  │                                                │                │    │
│  ├────────────────────────────────────────────────│────────────────┤    │
│  │                        ...                     │                │    │
│  │               (espacio libre)                  │                │    │
│  │                        ...                     │                │    │
│  ├────────────────────────────────────────────────│────────────────┤    │
│  │                         HEAP                   │                │    │
│  │  ┌─────────────┐                               │                │    │
│  │  │   dato 1    │ ← HEAP_BASE  ←────────────────┘                │    │
│  │  ├─────────────┤                                                │    │
│  │  │   dato 2    │                                                │    │
│  │  ├─────────────┤                                                │    │
│  │  │   dato 3    │                                                │    │
│  │  ├─────────────┤                                                │    │
│  │  │             │ ← HEAPPOINTER (brk)                            │    │
│  │  └─────────────┘                                                │    │
│  │        ↑                                                        │    │
│  │    Crece hacia ARRIBA                                           │    │
│  │    (direcciones mayores)                                        │    │
│  │                                                                 │    │
│  └─────────────────────────────────────────────────────────────────┘    │
│  Direcciones BAJAS (0x0000...)                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

**Puntos clave:**
- El **STACK** crece hacia **ABAJO** (se usa `sub sp, sp, #n` para reservar)
- El **HEAP** crece hacia **ARRIBA** (se usa `brk` para expandir)
- Ambos crecen uno hacia el otro en el espacio de memoria

### Tipos de Instrucciones C3D

| Tipo | Formato | Ejemplo |
|------|---------|---------|
| Asignación | `x = y` | `t1 = HEAPPOINTER` |
| Operación binaria | `x = y op z` | `t3 = t1 + t2` |
| Operación unaria | `x = op y` | `t2 = -t1` |
| Copia indexada (lectura) | `x = y[i]` | `t1 = heap[t2]` |
| Copia indexada (escritura) | `x[i] = y` | `heap[t2] = t1` |
| Salto incondicional | `goto L` | `goto L1` |
| Salto condicional | `if x relop y goto L` | `if t1 > 0 goto L1` |
| Llamada a función | `call f, n` | `call print, 1` |
| Retorno | `return x` | `return t1` |
| Etiqueta | `L:` | `L1:` |

---

## Ejemplo: Declarar un Array `x = [1, 2, 3]`

### Paso 1: Código de 3 Direcciones (Python)

```python
# Memoria simulada
heap = [None] * 100      # Memoria dinámica (para datos)
stack = [None] * 100     # Pila (para referencias)
HEAPPOINTER = 0          # Apunta al siguiente espacio libre en heap
STACKPOINTER = 0         # Apunta al tope del stack

# === Declaración: x = [1, 2, 3] ===

t1 = STACKPOINTER        # t1 = posición en stack para guardar referencia
t2 = HEAPPOINTER         # t2 = inicio del array en heap

# Guardamos longitud del array (3 elementos)
heap[t2 + 0] = 3         
HEAPPOINTER = HEAPPOINTER + 1

# Guardamos cada elemento
heap[t2 + 1] = 1         # Elemento 0
HEAPPOINTER = HEAPPOINTER + 1

heap[t2 + 2] = 2         # Elemento 1
HEAPPOINTER = HEAPPOINTER + 1

heap[t2 + 3] = 3         # Elemento 2
HEAPPOINTER = HEAPPOINTER + 1

# Guardamos la referencia al array en el stack
stack[t1] = t2
STACKPOINTER = STACKPOINTER + 1
```

### Paso 2: Traducción a AArch64 (Estructuras Nativas)

En AArch64, usamos las estructuras nativas del sistema:
- **SP (Stack Pointer)**: Registro dedicado para la pila, crece hacia direcciones bajas
- **brk syscall**: Para solicitar memoria dinámica del heap al sistema operativo

```asm
.text
.global _start

_start:
    // ============================================================
    // INICIALIZACIÓN DEL HEAP (usando syscall brk)
    // ============================================================
    // brk(0) retorna la dirección actual del heap
    mov     x0, #0            // argumento: 0 = obtener dirección actual
    mov     x8, #214          // syscall número 214 = brk
    svc     #0                // llamada al sistema
    mov     x19, x0           // x19 = HEAP_BASE (dirección base del heap)
    mov     x21, x0           // x21 = HEAPPOINTER (inicio = base)

    // Solicitar más memoria para el heap (800 bytes)
    add     x0, x19, #800     // nueva dirección = base + 800 bytes
    mov     x8, #214          // syscall brk
    svc     #0                // el heap ahora tiene 800 bytes disponibles

    // ============================================================
    // INICIALIZACIÓN DEL STACK (usando SP nativo)
    // ============================================================
    // SP ya está inicializado por el sistema operativo
    // En AArch64, el stack crece hacia ABAJO (direcciones menores)
    // Reservamos espacio en el stack para nuestras variables
    
    sub     sp, sp, #64       // Reservar 64 bytes en el stack (8 variables de 8 bytes)
    mov     x20, sp           // x20 = STACK_BASE (referencia a nuestro frame)

    // ============================================================
    // CÓDIGO DEL PROGRAMA: x = [1, 2, 3]
    // ============================================================

    // === t1 = posición en stack (offset 0) ===
    mov     x10, #0           // t1 = 0 (primera posición del stack)

    // === t2 = HEAPPOINTER (posición actual en heap) ===
    mov     x11, x21          // t2 = dirección actual del heap

    // === heap[0] = 3 (longitud del array) ===
    mov     x0, #3            // valor a guardar = 3
    str     x0, [x21]         // *HEAPPOINTER = 3
    add     x21, x21, #8      // HEAPPOINTER += 8 bytes

    // === heap[1] = 1 (elemento 0) ===
    mov     x0, #1            // valor a guardar = 1
    str     x0, [x21]         // *HEAPPOINTER = 1
    add     x21, x21, #8      // HEAPPOINTER += 8 bytes

    // === heap[2] = 2 (elemento 1) ===
    mov     x0, #2            // valor a guardar = 2
    str     x0, [x21]         // *HEAPPOINTER = 2
    add     x21, x21, #8      // HEAPPOINTER += 8 bytes

    // === heap[3] = 3 (elemento 2) ===
    mov     x0, #3            // valor a guardar = 3
    str     x0, [x21]         // *HEAPPOINTER = 3
    add     x21, x21, #8      // HEAPPOINTER += 8 bytes

    // === stack[t1] = t2 (guardar referencia al array) ===
    // Usamos el stack nativo con offset desde x20
    str     x11, [x20, x10]   // stack[0] = dirección del array en heap

    // ============================================================
    // FIN DEL PROGRAMA
    // ============================================================
    // Restaurar el stack antes de salir
    add     sp, sp, #64       // Liberar espacio reservado

    // Llamada a exit(0)
    mov     x8, #93           // syscall exit
    mov     x0, #0            // código de salida = 0
    svc     #0
```

### Explicación de las Estructuras Nativas

#### HEAP con syscall `brk`

```asm
// Obtener dirección base del heap
mov     x0, #0            // brk(0) = obtener dirección actual
mov     x8, #214          // número de syscall brk
svc     #0
mov     x19, x0           // guardar dirección base

// Expandir el heap
add     x0, x19, #800     // solicitar 800 bytes más
mov     x8, #214
svc     #0                // heap expandido
```

El heap en sistemas reales:
- Se obtiene mediante la syscall `brk` (número 214 en AArch64 Linux)
- `brk(0)` retorna la dirección actual del final del heap
- `brk(nueva_direccion)` expande el heap hasta esa dirección
- Crece hacia direcciones **altas**

#### STACK con registro SP

```asm
// Reservar espacio (el stack crece hacia abajo)
sub     sp, sp, #64       // sp = sp - 64 (reservar 64 bytes)

// Acceder a variables en el stack
str     x0, [sp, #0]      // variable en offset 0
str     x1, [sp, #8]      // variable en offset 8
ldr     x2, [sp, #0]      // leer variable

// Liberar espacio al terminar
add     sp, sp, #64       // sp = sp + 64 (liberar)
```

El stack nativo en AArch64:
- Usa el registro especial `SP` (Stack Pointer)
- Crece hacia direcciones **bajas** (se resta para reservar)
- Debe estar alineado a 16 bytes
- Se libera automáticamente al retornar de funciones

---

## Resumen Visual

```
┌──────────────────────────────────────────────────────────────┐
│  C3D (Python)            │  AArch64 (Nativo)                 │
├──────────────────────────────────────────────────────────────┤
│  t2 = HEAPPOINTER        │  mov x11, x21                     │
│                          │                                   │
│  heap[t2] = 3            │  mov x0, #3                       │
│                          │  str x0, [x21]   // guardar       │
│                          │                                   │
│  HEAPPOINTER += 1        │  add x21, x21, #8  // +8 bytes    │
│                          │                                   │
│  (reservar stack)        │  sub sp, sp, #64  // crece abajo  │
│                          │                                   │
│  stack[t1] = t2          │  str x11, [x20, x10]              │
└──────────────────────────────────────────────────────────────┘
```

---

## Estructura en Memoria (AArch64)

Después de ejecutar `x = [1, 2, 3]`:

```
DIRECCIONES ALTAS
        │
        ▼
┌─────────────────────────────────────────────────────────────┐
│                         STACK                               │
│  ┌───────────────┬───────────────┬─────────────────────┐    │
│  │   (libre)     │   (libre)     │        ...          │    │
│  ├───────────────┼───────────────┼─────────────────────┤    │
│  │               │               │                     │    │
│  │  ← SP         │               │                     │    │
│  ├───────────────┼───────────────┼─────────────────────┤    │
│  │  0x10000      │   (libre)     │        ...          │    │
│  │  (ref heap)   │               │                     │    │
│  │  ← x20        │               │                     │    │
│  └───────────────┴───────────────┴─────────────────────┘    │
│         │        ↓ El stack CRECE hacia ABAJO               │
│         │                                                   │
│         │        ... (espacio libre) ...                    │
│         │                                                   │
│         │        ↑ El heap CRECE hacia ARRIBA               │
│  ┌──────│────────────────────────────────────────────────┐  │
│  │      ▼                    HEAP                        │  │
│  │  ┌────────┬────────┬────────┬────────┬────────┐       │  │
│  │  │   3    │   1    │   2    │   3    │        │       │  │
│  │  │ (long) │ [0]    │ [1]    │ [2]    │        │       │  │
│  │  ├────────┼────────┼────────┼────────┼────────┤       │  │
│  │  │0x10000 │0x10008 │0x10010 │0x10018 │0x10020 │       │  │
│  │  │        │        │        │        │← x21   │       │  │
│  │  │← x19   │        │        │        │(HEAPPTR)│      │  │
│  │  │(BASE)  │        │        │        │        │       │  │
│  │  └────────┴────────┴────────┴────────┴────────┘       │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
        ▲
        │
DIRECCIONES BAJAS
```

**Leyenda:**
- `x19` = HEAP_BASE (dirección inicial del heap, obtenida con `brk(0)`)
- `x21` = HEAPPOINTER (apunta al siguiente espacio libre en el heap)
- `x20` = Referencia al frame actual en el stack
- `SP` = Stack Pointer nativo (registro especial de AArch64)
- Cada celda ocupa **8 bytes** (64 bits)
- La referencia en el stack (`0x10000`) apunta al inicio del array en el heap

---

## Instrucciones AArch64 - Explicación Detallada

### Instrucciones de Carga de Direcciones

#### `adrp xD, simbolo`
**Address of Page** - Calcula la dirección de la página (4KB) donde está el símbolo.

```asm
adrp x19, heap    // x19 = dirección base de la página donde está "heap"
```

- AArch64 usa direcciones de 64 bits, pero las instrucciones son de 32 bits
- No se puede cargar una dirección completa en una sola instrucción
- `adrp` carga los bits superiores (los más significativos)
- El resultado está alineado a 4KB (los 12 bits inferiores son 0)

#### `add xD, xS, :lo12:simbolo`
**Add Low 12 bits** - Suma los 12 bits inferiores de la dirección.

```asm
add x19, x19, :lo12:heap    // x19 = x19 + offset dentro de la página
```

- Complementa a `adrp` para obtener la dirección exacta
- `:lo12:` es una directiva que extrae los 12 bits bajos del símbolo

**Patrón completo para cargar dirección:**
```asm
adrp x19, heap           // Página base
add  x19, x19, :lo12:heap // + offset = dirección exacta
```

---

### Instrucciones de Movimiento

#### `mov xD, #inmediato`
**Move Immediate** - Carga un valor constante en un registro.

```asm
mov x21, #0     // x21 = 0
mov x0, #3      // x0 = 3
```

- El valor inmediato tiene límites (16 bits directos, o patrones especiales)
- Para valores más grandes se usa `movz`, `movk` en secuencia

#### `mov xD, xS`
**Move Register** - Copia el valor de un registro a otro.

```asm
mov x10, x22    // x10 = x22 (copia el valor)
```

- Es un alias de `orr xD, xzr, xS` (OR con registro cero)
- `xzr` es el registro que siempre contiene 0

---

### Instrucciones Aritméticas

#### `add xD, xS, #inmediato`
**Add Immediate** - Suma un valor constante a un registro.

```asm
add x21, x21, #1    // x21 = x21 + 1 (incrementar HEAPPOINTER)
add x2, x11, #1     // x2 = x11 + 1 (calcular índice)
```

#### `add xD, xS1, xS2`
**Add Register** - Suma dos registros.

```asm
add x0, x1, x2      // x0 = x1 + x2
```

#### `lsl xD, xS, #n`
**Logical Shift Left** - Desplaza bits hacia la izquierda.

```asm
lsl x1, x11, #3     // x1 = x11 << 3 = x11 * 8
```

- Desplazar n bits a la izquierda equivale a multiplicar por 2^n
- `<< 3` = `* 8` (porque 2³ = 8)
- Se usa para calcular offsets en bytes (cada elemento = 8 bytes)

**Ejemplo visual:**
```
x11 = 2 (binario: 0010)
lsl x1, x11, #3
x1 = 16 (binario: 00010000)

Verificación: 2 * 8 = 16 ✓
```

---

### Instrucciones de Memoria

#### `str xS, [xBase, xOffset]`
**Store Register** - Guarda un valor en memoria.

```asm
str x0, [x19, x1]    // memoria[x19 + x1] = x0
```

- `x0` = valor a guardar
- `x19` = dirección base (inicio del heap)
- `x1` = offset en bytes
- Guarda 8 bytes (64 bits) porque usamos registros `x`

**Modos de direccionamiento:**
```asm
str x0, [x19]              // memoria[x19] = x0
str x0, [x19, #8]          // memoria[x19 + 8] = x0
str x0, [x19, x1]          // memoria[x19 + x1] = x0
str x0, [x19, x1, lsl #3]  // memoria[x19 + x1*8] = x0
```

#### `ldr xD, [xBase, xOffset]`
**Load Register** - Carga un valor desde memoria.

```asm
ldr x0, [x19, x1]    // x0 = memoria[x19 + x1]
```

- Operación inversa a `str`
- Lee 8 bytes de memoria hacia el registro

---

### Instrucciones de Sistema

#### `svc #0`
**Supervisor Call** - Realiza una llamada al sistema operativo (syscall).

```asm
mov x8, #93    // Número de syscall (93 = exit en Linux)
mov x0, #0     // Argumento: código de salida
svc #0         // Ejecutar syscall
```

**Convención de syscalls en AArch64 Linux:**

| Registro | Propósito |
|----------|-----------|
| `x8` | Número de syscall |
| `x0` | Primer argumento / valor de retorno |
| `x1` | Segundo argumento |
| `x2` | Tercer argumento |
| `x0-x7` | Argumentos adicionales |

**Syscalls comunes:**

| Número | Nombre | Descripción |
|--------|--------|-------------|
| 63 | read | Leer de archivo |
| 64 | write | Escribir a archivo |
| 93 | exit | Terminar programa |

---

### Directivas del Ensamblador

#### `.data`
Define la sección de datos (variables globales).

```asm
.data
    heap:  .zero 800    // Reserva 800 bytes inicializados a 0
```

#### `.text`
Define la sección de código (instrucciones).

```asm
.text
.global _start
```

#### `.global simbolo`
Hace visible el símbolo para el linker (punto de entrada).

```asm
.global _start    // _start es el punto de entrada del programa
```

#### `.zero n`
Reserva n bytes inicializados a cero.

```asm
heap: .zero 800    // 100 elementos * 8 bytes = 800 bytes
```

---

## Registros Utilizados

| Registro | Uso en el programa |
|----------|-------------------|
| `x0-x2` | Temporales para cálculos |
| `x8` | Número de syscall |
| `x10` | Temporal t1 (posición en stack) |
| `x11` | Temporal t2 (posición en heap) |
| `x19` | Puntero base al heap |
| `x20` | Puntero base al stack |
| `x21` | HEAPPOINTER |
| `x22` | STACKPOINTER |
| `xzr` | Registro cero (siempre vale 0) |

---

## Flujo Completo de una Operación

**Ejemplo: `heap[t2 + 1] = 1`**

```asm
mov x0, #1            // 1. Cargar valor (1) en x0
add x2, x11, #1       // 2. Calcular índice: x2 = t2 + 1
lsl x1, x2, #3        // 3. Convertir a bytes: x1 = (t2+1) * 8
str x0, [x19, x1]     // 4. Guardar: heap[offset] = 1
```

```
Paso 1: x0 = 1
Paso 2: x2 = 0 + 1 = 1
Paso 3: x1 = 1 * 8 = 8
Paso 4: memoria[heap + 8] = 1
```

---

## Resumen de Instrucciones AArch64

| Instrucción | Descripción |
|-------------|-------------|
| `mov xD, #valor` | Carga un valor inmediato en registro |
| `mov xD, xS` | Copia valor entre registros |
| `add xD, xS, #n` | Suma: xD = xS + n |
| `lsl xD, xS, #n` | Shift left: xD = xS << n (multiplicar por 2^n) |
| `str xS, [xBase, xOffset]` | Guarda xS en memoria[xBase + xOffset] |
| `ldr xD, [xBase, xOffset]` | Carga desde memoria a xD |
| `adrp xD, simbolo` | Carga dirección de página del símbolo |
| `svc #0` | Llamada al sistema operativo |

El patrón `lsl x1, x11, #3` multiplica por 8 porque cada elemento ocupa 8 bytes (64 bits) en AArch64.
