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
add x19, sp, #0
and x19, x19, #15
cbz x19, L1
sub sp, sp, #8
mov x19, #1
b L0
L1:
mov x19, #0
L0:
bl _native_time
cbz x19, L2
add sp, sp, #8
b L3
L2:
L3:
sub sp, sp, #8
str x0, [sp, #0]
// Declaracion de variable: t (int) en [FP, #-8]
ldr x9, [sp, #0]
add sp, sp, #8
sub sp, sp, #8
str x9, [x29, #-8]
ldr x9, [x29, #-8]
sub sp, sp, #8
str x9, [sp, #0]
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
_native_time:
stp x29, x30, [sp, #-16]!
mov x29, sp
mov x0, #0
ldr x1, =native_time_spec
mov x8, #113
svc #0
ldr x0, =native_time_spec
ldr x0, [x0, #0]
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
