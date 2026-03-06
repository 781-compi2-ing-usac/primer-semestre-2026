.global _start
.section .bss
    buffer: .skip 32
.section .text
_start:
    // Cargando entero: 2
    mov x9, #2
    sub sp, sp, #8
    str x9, [sp, #0]
    // Cargando entero: 2
    mov x9, #2
    sub sp, sp, #8
    str x9, [sp, #0]
    // Cargando entero: 33
    mov x9, #33
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
    // Cargando entero: 2
    mov x9, #2
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
    // Cargando el número de syscall para write
    mov x8, #64
    svc #0
    // Imprimiendo un salto de línea
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
