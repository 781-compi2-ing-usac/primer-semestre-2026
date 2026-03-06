func bonificarNotas(notas *[]int32, puntosExtra int32) {
    for i := 0; i < len(notas); i++ {
        notas[i] = notas[i] + puntosExtra
    }
}

// ==========================================
// 3.6 Función main()
// ==========================================
func main() { 
    fmt.Println("=== INICIO DE CALIFICACION: FUNCIONES ===")

    // ==========================================
    // 3.7 Hoisting
    // ==========================================
    fmt.Println("\n--- 3.7 HOISTING ---")
    funcionHoisting()

    // ==========================================
    // 3.1 Función no recursiva sin parámetros
    // ==========================================
    fmt.Println("\n--- 3.1 FUNCION SIN PARAMETROS ---")
    mostrarBienvenida()

    // ==========================================
    // 3.2 Función con parámetros no recursiva
    // ==========================================
    fmt.Println("\n--- 3.2 FUNCION CON PARAMETROS ---")
    resultado := sumarNumeros(15, 25)
    fmt.Println("Resultado de sumarNumeros(15, 25):", resultado)

    // ==========================================
    // 3.3 Funciones por referencia
    // ==========================================
    fmt.Println("\n--- 3.3 FUNCIONES POR REFERENCIA ---")
    var numeroBase int32 = 10
    fmt.Println("Valor antes de la función por referencia:", numeroBase)
    duplicarPorReferencia(&numeroBase)
    fmt.Println("Valor después de la función por referencia (debe ser 20):", numeroBase)

    fmt.Println("\n--- 3.3 FUNCIONES POR REFERENCIA (CON ARREGLOS) ---")
    misNotas := []int32{60, 75, 82, 90}
    
    fmt.Println("Notas ANTES de la bonificación:")
    fmt.Println("Nota 1:", misNotas[0], "| Nota 2:", misNotas[1], "| Nota 3:", misNotas[2], "| Nota 4:", misNotas[3])
    
    bonificarNotas(&misNotas, 5)
    
    fmt.Println("\nNotas DESPUES de la bonificación (+5 puntos):")
    fmt.Println("Nota 1:", misNotas[0], "| Nota 2:", misNotas[1], "| Nota 3:", misNotas[2], "| Nota 4:", misNotas[3])

    // ==========================================
    // 3.4 Función no recursiva múltiple retorno
    // ==========================================
    fmt.Println("\n--- 3.4 FUNCION MULTIPLE RETORNO ---")
    sum, res := operacionesBasicas(50, 20)
    fmt.Println("Operaciones 50 y 20 -> Suma:", sum, ", Resta:", res)

    // ==========================================
    // 3.5 Función recursiva con un retorno 
    // ==========================================
    fmt.Println("\n--- 3.5 FUNCION RECURSIVA (UN RETORNO) ---")
    fact := factorial(5)
    fmt.Println("Factorial de 5:", fact)

    // ==========================================
    // 3.6 Función recursiva con múltiple retorno
    // ==========================================
    fmt.Println("\n--- 3.6 FUNCION RECURSIVA (MULTIPLE RETORNO) ---")
    fib, llamadas := fibonacciAvanzado(4)
    fmt.Println("Fibonacci de 4:", fib, "| Llamadas recursivas totales:", llamadas)

    fmt.Println("\n=== FIN DE CALIFICACION: FUNCIONES ===")
}

// 3.7 Función declarada debajo del main (Prueba de Hoisting)
func funcionHoisting() {
    fmt.Println(">>> Ejecutando funcionHoisting() declarada correctamente debajo del main.")
}

// 3.1 Función sin parámetros
func mostrarBienvenida() {
    fmt.Println("¡Bienvenido al sistema de evaluación de funciones Golampi!")
}

// 3.2 Función con parámetros
func sumarNumeros(a int32, b int32) int32 {
    return a + b
}

// 3.3 Función por referencia
func duplicarPorReferencia(valor *int32) {
    /*
    Como en los ejemplos del enunciado al trabajar con punteros no utilizamos *, 
    si se inicializa con * el parametro, se debe trabajar por referencia aunque en la sintaxis de golang no lo permita
    */
    valor = valor * 2
}

// 3.4 Función múltiple retorno
func operacionesBasicas(a int32, b int32) (int32, int32) {
    suma := a + b
    resta := a - b
    return suma, resta
}

// 3.5 Función recursiva (1 retorno)
func factorial(n int32) int32 {
    if n <= 1 {
        return 1
    }
    return n * factorial(n - 1)
}

// 3.6 Función recursiva (Múltiple retorno)
func fibonacciAvanzado(n int32) (int32, int32) {
    if n <= 1 {
        return n, 1
    }
    
    val1, pasos1 := fibonacciAvanzado(n - 1)
    val2, pasos2 := fibonacciAvanzado(n - 2)
    
    nuevoValor := val1 + val2
    totalPasos := pasos1 + pasos2 + 1
    
    return nuevoValor, totalPasos
}

/*
=== INICIO DE CALIFICACION: FUNCIONES ===

--- 3.7 HOISTING ---
>>> Ejecutando funcionHoisting() declarada correctamente debajo del main.

--- 3.1 FUNCION SIN PARAMETROS ---
¡Bienvenido al sistema de evaluación de funciones Golampi!

--- 3.2 FUNCION CON PARAMETROS ---
Resultado de sumarNumeros(15, 25): 40

--- 3.3 FUNCIONES POR REFERENCIA ---
Valor antes de la función por referencia: 10
Valor después de la función por referencia (debe ser 20): 20

--- 3.3 FUNCIONES POR REFERENCIA (CON ARREGLOS) ---
Notas ANTES de la bonificación:
Nota 1: 60 | Nota 2: 75 | Nota 3: 82 | Nota 4: 90

Notas DESPUES de la bonificación (+5 puntos):
Nota 1: 65 | Nota 2: 80 | Nota 3: 87 | Nota 4: 95

--- 3.4 FUNCION MULTIPLE RETORNO ---
Operaciones 50 y 20 -> Suma: 70 , Resta: 30

--- 3.5 FUNCION RECURSIVA (UN RETORNO) ---
Factorial de 5: 120

--- 3.6 FUNCION RECURSIVA (MULTIPLE RETORNO) ---
Fibonacci de 4: 3 | Llamadas recursivas totales: 9

=== FIN DE CALIFICACION: FUNCIONES ===
*/