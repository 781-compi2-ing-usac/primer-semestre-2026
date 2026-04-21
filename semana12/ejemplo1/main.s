.global _start
.section .bss
buffer: .skip 32
native_time_spec: .skip 16
heap_base: .skip 1048576
heap_end:
.section .text
_start:
// Configurando el frame pointer
mov x29, sp
// Inicializando heap pointer y limite de heap
ldr x20, =heap_base
ldr x21, =heap_end
mov x9, #5
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #4
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #2
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #8
sub sp, sp, #8
str x9, [sp, #0]
// Array literal: reservando bloque continuo en heap
// Heap alloc de 56 bytes
mov x13, x20
mov x9, #56
add x10, x20, x9
cmp x10, x21
b.hi _panic_oom
mov x20, x10
// Array literal: escribiendo header (rank y dimensiones)
mov x9, #1
str x9, [x13, #0]
mov x9, #5
str x9, [x13, #8]
// Array literal: escribiendo elementos en orden row-major
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x13, #48]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x13, #40]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x13, #32]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x13, #24]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x13, #16]
sub sp, sp, #8
str x13, [sp, #0]
// Declaracion de variable: arr (array<int> rank=1 dims=5) en [FP, #-8]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-8]
// Preparando argumentos de llamada para 'bubble_sort'
// arg1 by_ref: pasando direccion de variable
mov x0, x29
sub x0, x0, #8
// Alineando SP a 16 bytes antes de bl
add x14, sp, #0
and x14, x14, #15
cbz x14, L0
sub sp, sp, #8
// Llamada a funcion con padding temporal de stack
bl _fn_bubble_sort
add sp, sp, #8
b L1
L0:
// Llamada a funcion sin padding extra
bl _fn_bubble_sort
L1:
mov x9, #0
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x10, [x29, #-8]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
// Array access: cargando puntero base del array
ldr x10, [x29, #-8]
ldr x12, [sp, #0]
add sp, sp, #8
// Array access: calculando direccion lineal del elemento
mov x11, #8
mul x12, x12, x11
add x11, x10, x12
add x11, x11, #16
// Array access: leyendo valor desde heap
ldr x12, [x11, #0]
sub sp, sp, #8
str x12, [sp, #0]
// Imprimiendo el resultado de la expresion
ldr x0, [sp, #0]
add sp, sp, #8
bl itoa
// Retorno: A0 con puntero al buffer, A1 con longitud
// Preparando argumentos para syscall write
mov x2, x1
mov x1, x0
mov x0, #1
mov x8, #64
svc #0
// Preparando salto de línea
mov x0, #1
ldr x1, =newline
mov x2, #1
mov x8, #64
svc #0
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x10, [x29, #-8]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
// Array access: cargando puntero base del array
ldr x10, [x29, #-8]
ldr x12, [sp, #0]
add sp, sp, #8
// Array access: calculando direccion lineal del elemento
mov x11, #8
mul x12, x12, x11
add x11, x10, x12
add x11, x11, #16
// Array access: leyendo valor desde heap
ldr x12, [x11, #0]
sub sp, sp, #8
str x12, [sp, #0]
// Imprimiendo el resultado de la expresion
ldr x0, [sp, #0]
add sp, sp, #8
bl itoa
// Retorno: A0 con puntero al buffer, A1 con longitud
// Preparando argumentos para syscall write
mov x2, x1
mov x1, x0
mov x0, #1
mov x8, #64
svc #0
// Preparando salto de línea
mov x0, #1
ldr x1, =newline
mov x2, #1
mov x8, #64
svc #0
mov x9, #2
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x10, [x29, #-8]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
// Array access: cargando puntero base del array
ldr x10, [x29, #-8]
ldr x12, [sp, #0]
add sp, sp, #8
// Array access: calculando direccion lineal del elemento
mov x11, #8
mul x12, x12, x11
add x11, x10, x12
add x11, x11, #16
// Array access: leyendo valor desde heap
ldr x12, [x11, #0]
sub sp, sp, #8
str x12, [sp, #0]
// Imprimiendo el resultado de la expresion
ldr x0, [sp, #0]
add sp, sp, #8
bl itoa
// Retorno: A0 con puntero al buffer, A1 con longitud
// Preparando argumentos para syscall write
mov x2, x1
mov x1, x0
mov x0, #1
mov x8, #64
svc #0
// Preparando salto de línea
mov x0, #1
ldr x1, =newline
mov x2, #1
mov x8, #64
svc #0
mov x9, #3
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x10, [x29, #-8]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
// Array access: cargando puntero base del array
ldr x10, [x29, #-8]
ldr x12, [sp, #0]
add sp, sp, #8
// Array access: calculando direccion lineal del elemento
mov x11, #8
mul x12, x12, x11
add x11, x10, x12
add x11, x11, #16
// Array access: leyendo valor desde heap
ldr x12, [x11, #0]
sub sp, sp, #8
str x12, [sp, #0]
// Imprimiendo el resultado de la expresion
ldr x0, [sp, #0]
add sp, sp, #8
bl itoa
// Retorno: A0 con puntero al buffer, A1 con longitud
// Preparando argumentos para syscall write
mov x2, x1
mov x1, x0
mov x0, #1
mov x8, #64
svc #0
// Preparando salto de línea
mov x0, #1
ldr x1, =newline
mov x2, #1
mov x8, #64
svc #0
mov x9, #4
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x10, [x29, #-8]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
// Array access: cargando puntero base del array
ldr x10, [x29, #-8]
ldr x12, [sp, #0]
add sp, sp, #8
// Array access: calculando direccion lineal del elemento
mov x11, #8
mul x12, x12, x11
add x11, x10, x12
add x11, x11, #16
// Array access: leyendo valor desde heap
ldr x12, [x11, #0]
sub sp, sp, #8
str x12, [sp, #0]
// Imprimiendo el resultado de la expresion
ldr x0, [sp, #0]
add sp, sp, #8
bl itoa
// Retorno: A0 con puntero al buffer, A1 con longitud
// Preparando argumentos para syscall write
mov x2, x1
mov x1, x0
mov x0, #1
mov x8, #64
svc #0
// Preparando salto de línea
mov x0, #1
ldr x1, =newline
mov x2, #1
mov x8, #64
svc #0
// Terminando el programa
mov x0, #0
mov x8, #93
svc #0
_panic_oob:
mov x0, #2
mov x8, #93
svc #0
_panic_oom:
mov x0, #1
mov x8, #93
svc #0
_fn_bubble_sort:
// Prologo de funcion foreign 'bubble_sort'
stp x29, x30, [sp, #-16]!
mov x29, sp
// Materializando parametro 'a' (by_ref) en frame local
sub sp, sp, #8
str x0, [x29, #-8]
ldr x11, [x29, #-8]
ldr x9, [x11, #0]
sub sp, sp, #8
str x9, [sp, #0]
// Preparando argumentos de llamada para 'len'
// arg1 by_value: cargando valor en registro de argumento
ldr x0, [sp, #0]
add sp, sp, #8
// Alineando SP a 16 bytes antes de bl
add x14, sp, #0
and x14, x14, #15
cbz x14, L3
sub sp, sp, #8
// Llamada a funcion con padding temporal de stack
bl _native_len
add sp, sp, #8
b L4
L3:
// Llamada a funcion sin padding extra
bl _native_len
L4:
sub sp, sp, #8
str x0, [sp, #0]
// Declaracion de variable: n (int) en [FP, #-16]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-16]
mov x9, #0
sub sp, sp, #8
str x9, [sp, #0]
// Declaracion de variable: i (int) en [FP, #-24]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-24]
L5:
ldr x9, [x29, #-24]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [x29, #-16]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
cmp x10, x9
cset x9, lt
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
cbz x9, L6
mov x9, #0
sub sp, sp, #8
str x9, [sp, #0]
// Declaracion de variable: j (int) en [FP, #-32]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-32]
L7:
ldr x9, [x29, #-32]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [x29, #-16]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [x29, #-24]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
sub x9, x10, x9
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
sub x9, x10, x9
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
cmp x10, x9
cset x9, lt
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
cbz x9, L8
ldr x9, [x29, #-32]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
// Array access: cargando puntero base del array
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
ldr x12, [sp, #0]
add sp, sp, #8
// Array access: calculando direccion lineal del elemento
mov x11, #8
mul x12, x12, x11
add x11, x10, x12
add x11, x11, #16
// Array access: leyendo valor desde heap
ldr x12, [x11, #0]
sub sp, sp, #8
str x12, [sp, #0]
// Declaracion de variable: left (int) en [FP, #-40]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-40]
ldr x9, [x29, #-32]
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
add x9, x10, x9
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
// Array access: cargando puntero base del array
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
ldr x12, [sp, #0]
add sp, sp, #8
// Array access: calculando direccion lineal del elemento
mov x11, #8
mul x12, x12, x11
add x11, x10, x12
add x11, x11, #16
// Array access: leyendo valor desde heap
ldr x12, [x11, #0]
sub sp, sp, #8
str x12, [sp, #0]
// Declaracion de variable: right (int) en [FP, #-48]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-48]
ldr x9, [x29, #-40]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [x29, #-48]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
cmp x10, x9
cset x9, gt
sub sp, sp, #8
str x9, [sp, #0]
// Evaluando condicion del if
ldr x9, [sp, #0]
add sp, sp, #8
cbz x9, L9
ldr x9, [x29, #-32]
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [x29, #-48]
sub sp, sp, #8
str x9, [sp, #0]
ldr x12, [sp, #0]
add sp, sp, #8
ldr x13, [sp, #0]
add sp, sp, #8
// Array assignment: cargando puntero base del array destino
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
// Array assignment: calculando direccion lineal del elemento
mov x11, #8
mul x13, x13, x11
add x11, x10, x13
add x11, x11, #16
// Array assignment: escribiendo valor en heap
str x12, [x11, #0]
ldr x9, [x29, #-32]
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
add x9, x10, x9
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
// Array index rank-1: bounds check sobre dimension 0
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
cmp x9, #0
b.lt _panic_oob
ldr x11, [x10, #8]
cmp x9, x11
b.ge _panic_oob
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [x29, #-40]
sub sp, sp, #8
str x9, [sp, #0]
ldr x12, [sp, #0]
add sp, sp, #8
ldr x13, [sp, #0]
add sp, sp, #8
// Array assignment: cargando puntero base del array destino
ldr x11, [x29, #-8]
ldr x10, [x11, #0]
// Array assignment: calculando direccion lineal del elemento
mov x11, #8
mul x13, x13, x11
add x11, x10, x13
add x11, x11, #16
// Array assignment: escribiendo valor en heap
str x12, [x11, #0]
L9:
ldr x9, [x29, #-32]
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
add x9, x10, x9
sub sp, sp, #8
str x9, [sp, #0]
// Asignacion a variable: j en [base, #-32]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x29, #-32]
add sp, sp, #16
b L7
L8:
ldr x9, [x29, #-24]
sub sp, sp, #8
str x9, [sp, #0]
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
add x9, x10, x9
sub sp, sp, #8
str x9, [sp, #0]
// Asignacion a variable: i en [base, #-24]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x29, #-24]
add sp, sp, #8
b L5
L6:
add sp, sp, #16
mov x0, #0
b L2
L2:
// Epilogo de funcion: restaurar SP, FP y RA
mov sp, x29
ldp x29, x30, [sp], #16
ret
_native_len:
// Native len(array): prologo
stp x29, x30, [sp, #-16]!
mov x29, sp
// Native len(array): leer dimension 0 del header
ldr x0, [x0, #8]
// Native len(array): epilogo
ldp x29, x30, [sp], #16
ret
itoa:
// x0 = integer
// returns:
// x0 = buffer ptr
// x1 = length
ldr x2, =buffer
add x2, x2, #31
mov w3, #0
strb w3, [x2]
mov x5, x0
mov x4, #10
// Sign Flag 
mov x10, #0
// Check if negative 
cmp x5, #0
bge loop
neg x5, x5
mov x10, #1
loop:
udiv x6, x5, x4
msub x7, x6, x4, x5
add x7, x7, #48
sub x2, x2, #1
strb w7, [x2]
mov x5, x6
cbnz x6, loop
cmp x10, #0
beq done
sub x2, x2, #1
mov w7, #45
strb w7, [x2]
done:
ldr x3, =buffer
add x3, x3, #31
sub x1, x3, x2
mov x0, x2
ret
.section .rodata
newline: .asciz "\n"

