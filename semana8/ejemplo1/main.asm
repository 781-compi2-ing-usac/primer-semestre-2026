.global _start
.section .bss
buffer: .skip 32
.section .text
_start:
// Configurando el frame pointer
mov x29, sp
// Cargando entero: 4
mov x9, #4
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 5
mov x9, #5
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de suma/resta: -
// Evaluando el segundo operando
ldr x9, [sp, #0]
add sp, sp, #8
// Evaluando el primer operando
ldr x10, [sp, #0]
add sp, sp, #8
// Restando T1 con T0
sub x9, x10, x9
sub sp, sp, #8
str x9, [sp, #0]
// Declaración de variable: x (int) en [FP, #-8]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-8]
// Referencia a variable: x en [FP, #-8]
ldr x9, [x29, #-8]
sub sp, sp, #8
str x9, [sp, #0]
// Cargando entero: 6
mov x9, #6
sub sp, sp, #8
str x9, [sp, #0]
// Visitando expresión de desigualdad: <
ldr x9, [sp, #0]
add sp, sp, #8
ldr x10, [sp, #0]
add sp, sp, #8
// Comparando T1 < T0
cmp x10, x9
cset x9, lt
sub sp, sp, #8
str x9, [sp, #0]
// Evaluando condición del if
ldr x9, [sp, #0]
add sp, sp, #8
cbz x9, L0
// Cuerpo del if
// Entrando a nuevo bloque/scope
// Referencia a variable: x en [FP, #-8]
ldr x9, [x29, #-8]
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
L0:
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
