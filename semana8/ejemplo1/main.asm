.global _start
.section .bss
buffer: .skip 32
.section .text
_start:
// Configurando el frame pointer
mov x29, sp
// Cargando entero: 0
mov x9, #0
sub sp, sp, #8
str x9, [sp, #0]
// Declaración de variable: i (int) en [FP, #-8]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-8]
L0:
// Referencia a variable: i en [FP, #-8]
ldr x9, [x29, #-8]
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 3
mov x9, #3
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de desigualdad: &lt;
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
// Comparando T1 &lt; T0
cmp x10, x9
cset x9, lt
sub sp, sp, #8
str x9, [sp, #0]
// Evaluando condición del while
ldr x9, [sp, #0]
add sp, sp, #8
cbz x9, L1
// Cuerpo del while
// Entrando a nuevo bloque/scope
// Cargando entero: 0
mov x9, #0
sub sp, sp, #8
str x9, [sp, #0]
// Declaración de variable: j (int) en [FP, #-16]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-16]
L2:
// Referencia a variable: j en [FP, #-16]
ldr x9, [x29, #-16]
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 3
mov x9, #3
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de desigualdad: &lt;
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
// Comparando T1 &lt; T0
cmp x10, x9
cset x9, lt
sub sp, sp, #8
str x9, [sp, #0]
// Evaluando condición del while
ldr x9, [sp, #0]
add sp, sp, #8
cbz x9, L3
// Cuerpo del while
// Entrando a nuevo bloque/scope
// Referencia a variable: j en [FP, #-16]
ldr x9, [x29, #-16]
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 1
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de igualdad: ==
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
// Comparando T1 == T0
cmp x10, x9
cset x9, eq
sub sp, sp, #8
str x9, [sp, #0]
// Evaluando condición del if
ldr x9, [sp, #0]
add sp, sp, #8
cbz x9, L4
// Cuerpo del if
// Entrando a nuevo bloque/scope
// Break: saltar al final del while
b L3
L4:
// Referencia a variable: i en [FP, #-8]
ldr x9, [x29, #-8]
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 10
mov x9, #10
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de producto: *
// Evaluando el primer operando
ldr x9, [sp, #0]
add sp, sp, #8
// Evaluando el segundo operando
ldr x10, [sp, #0]
add sp, sp, #8
// Multiplicando T0 con T1
mul x9, x9, x10
sub sp, sp, #8
str x9, [sp, #0]
// Referencia a variable: j en [FP, #-16]
ldr x9, [x29, #-16]
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de suma/resta: +
// Evaluando el primer operando
ldr x9, [sp, #0]
add sp, sp, #8
// Evaluando el segundo operando
ldr x10, [sp, #0]
add sp, sp, #8
// Sumando T0 con T1
add x9, x9, x10
sub sp, sp, #8
str x9, [sp, #0]
// Imprimiendo el resultado de la expresión
// Cargando el valor a imprimir en A0
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
// Referencia a variable: j en [FP, #-16]
ldr x9, [x29, #-16]
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 1
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de suma/resta: +
// Evaluando el primer operando
ldr x9, [sp, #0]
add sp, sp, #8
// Evaluando el segundo operando
ldr x10, [sp, #0]
add sp, sp, #8
// Sumando T0 con T1
add x9, x9, x10
sub sp, sp, #8
str x9, [sp, #0]
// Asignación a variable: j en [FP, #-16]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x29, #-16]
b L2
L3:
// Referencia a variable: i en [FP, #-8]
ldr x9, [x29, #-8]
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 1
mov x9, #1
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de suma/resta: +
// Evaluando el primer operando
ldr x9, [sp, #0]
add sp, sp, #8
// Evaluando el segundo operando
ldr x10, [sp, #0]
add sp, sp, #8
// Sumando T0 con T1
add x9, x9, x10
sub sp, sp, #8
str x9, [sp, #0]
// Asignación a variable: i en [FP, #-8]
ldr x9, [sp, #0]
add sp, sp, #8
str x9, [x29, #-8]
// Saliendo del bloque, recuperando 8 bytes
add sp, sp, #8
b L0
L1:
// Terminando el programa
mov x0, #0
mov x8, #93
svc #0
itoa:
// x0 = integer
// returns:
// x0 = buffer ptr
// x1 = length
ldr x2, =buffer
add x2, x2, #31
mov w3, #0
strb w3, [x2]
mov x4, #10
mov x5, x0
loop:
udiv x6, x5, x4
msub x7, x6, x4, x5
add x7, x7, #48
sub x2, x2, #1
strb w7, [x2]
mov x5, x6
cbnz x6, loop
ldr x3, =buffer
add x3, x3, #31
sub x1, x3, x2
mov x0, x2
ret
.section .rodata
newline: .asciz "\n"
