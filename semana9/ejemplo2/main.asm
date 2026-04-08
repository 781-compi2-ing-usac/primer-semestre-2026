
    .global _start
.section .bss
buffer: .skip 32
.section .text
_start:
    // Cargando entero: 5
    mov x9, #5
    // Cargando entero: 3
    mov x10, #3
    // Visitando expresión relacional: <
    cmp x9, x10
    cset x11, lt
    // Imprimiendo valor
    mov x0, x11
    bl itoa
    mov x2, x1
    mov x1, x0
    mov x0, #1
    mov x8, #64
    svc #0
    // Salto de línea
    mov x0, #1
    ldr x1, =newline
    mov x2, #1
    mov x8, #64
    svc #0
    // Fin del programa
    mov x0, #0
    mov x8, #93
    svc #0
itoa:
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