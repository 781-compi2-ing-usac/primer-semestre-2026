
.global _start
.section .bss
buffer: .skip 32
heap_base: .skip 1048576
heap_end:
.section .text
_start:
	// Configurando el frame pointer
	mov x29, sp
	// Inicializando heap pointer y límite de heap
	ldr x20, =heap_base
	ldr x21, =heap_end
	// Cargando entero: 1
	mov x9, #1
	sub sp, sp, #8
	str x9, [sp, #0]
	// Cargando entero: 2
	mov x9, #2
	sub sp, sp, #8
	str x9, [sp, #0]
	// Cargando entero: 3
	mov x9, #3
	sub sp, sp, #8
	str x9, [sp, #0]
	// Heap alloc de 40 bytes
	mov x13, x20
	mov x9, #40
	add x10, x20, x9
	cmp x10, x21
	b.hi _panic_oom
	mov x20, x10
	mov x9, #1
	str x9, [x13, #0]
	mov x9, #3
	str x9, [x13, #8]
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
	// Declaración de variable: a (array<int>) en [FP, #-8]
	ldr x9, [sp, #0]
	add sp, sp, #8
	sub sp, sp, #8
	str x9, [x29, #-8]
	ldr x10, [x29, #-8]
	// Cargando entero: 1
	mov x9, #1
	sub sp, sp, #8
	str x9, [sp, #0]
	ldr x9, [sp, #0]
	add sp, sp, #8
	cmp x9, #0
	b.lt _panic_oob
	ldr x11, [x10, #8]
	cmp x9, x11
	b.ge _panic_oob
	mov x12, #8
	mul x12, x9, x12
	add x11, x10, x12
	add x11, x11, #16
	ldr x12, [x11, #0]
	sub sp, sp, #8
	str x12, [sp, #0]
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
