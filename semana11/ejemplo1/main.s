.global _start

.section .text

_start:
    bl main

    // exit(0)
    mov x0, #0
    mov x8, #93     // syscall: exit
    svc #0


// ---------------------
// int sum(int a, int b)
// x0 = a, x1 = b
// return x0
// ---------------------
sum:
    // Prologue (explicit)
    sub sp, sp, #16        // allocate stack space
    stp x29, x30, [sp]    // save FP and LR
    mov x29, sp

    // Body
    add x0, x0, x1

    // Epilogue (explicit)
    ldp x29, x30, [sp]    // restore FP and LR
    add sp, sp, #16        // deallocate stack
    ret


// ---------------------
// main()
// ---------------------
main:
    // Prologue (explicit)
    sub sp, sp, #16
    stp x29, x30, [sp]
    mov x29, sp

    // Call sum(1,2)
    mov x0, #1
    mov x1, #2
    bl sum          // result in x0 = 3

    // Convert result to string
    bl itoa         // x0 = buffer, x1 = length

    // write(stdout, buffer, length)
    mov x2, x1      // len
    mov x1, x0      // buf
    mov x0, #1      // stdout
    mov x8, #64     // syscall: write
    svc #0

    // print newline
    ldr x1, =newline
    mov x2, #1
    mov x0, #1
    mov x8, #64
    svc #0

    // Epilogue (explicit)
    ldp x29, x30, [sp]
    add sp, sp, #16
    ret


// ---------------------
// itoa (unchanged)
// ---------------------
itoa:
    ldr x2, =buffer
    add x2, x2, #31
    mov w3, #0
    strb w3, [x2]

    mov x5, x0
    mov x4, #10

    // Sign flag
    mov x10, #0

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


// ---------------------
// Data
// ---------------------
.section .bss
buffer: .skip 32

.section .rodata
newline: .asciz "\n"
