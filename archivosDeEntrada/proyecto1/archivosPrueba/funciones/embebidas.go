func main() {
    // ==========================================
    // 4.1 fmt.Println()
    // ==========================================
    fmt.Println("=== INICIO DE CALIFICACION: FUNCIONES EMBEBIDAS ===")
    fmt.Println("\n--- 4.1 FMT.PRINTLN ---")
    fmt.Println("La función fmt.Println permite imprimir", "múltiples", "parámetros.")
    fmt.Println("Tipos mixtos:", 100, true, 3.1416, 'A')

    // ==========================================
    // 4.2 len()
    // ==========================================
    fmt.Println("\n--- 4.2 LEN() ---")
    
    // len() en Cadenas de texto
    cadena := "Compiladores 2"
    longitudCadena := len(cadena)
    fmt.Println("La cadena '", cadena, "' tiene longitud:", longitudCadena)
    
    // len() en Arreglos
    arreglo := [5]int32{10, 20, 30, 40, 50}
    longitudArreglo := len(arreglo)
    fmt.Println("El arreglo tiene longitud:", longitudArreglo)

    // ==========================================
    // 4.3 now()
    // ==========================================
    fmt.Println("\n--- 4.3 NOW() ---")
    fechaActual := now()
    fmt.Println("Fecha y hora actual del sistema:", fechaActual)

    // ==========================================
    // 4.4 substr()
    // ==========================================
    fmt.Println("\n--- 4.4 SUBSTR() ---")
    textoBase := "Universidad San Carlos"
    
    sub1 := substr(textoBase, 0, 11)
    fmt.Println("Subcadena 0-11 de '", textoBase, "':", sub1)
    
    sub2 := substr(textoBase, 12, 10)
    fmt.Println("Subcadena 12-22 de '", textoBase, "':", sub2)

    // ==========================================
    // 4.5 typeOf()
    // ==========================================
    fmt.Println("\n--- 4.5 TYPEOF() ---")
    
    varEntero := 42
    varFlotante := 9.81
    varBooleano := false
    varCaracter := 'X'
    varTexto := "Golampi"
    varArreglo := [3]int32{1, 2, 3}
    
    fmt.Println("typeOf(varEntero):", typeOf(varEntero))       // "int32"
    fmt.Println("typeOf(varFlotante):", typeOf(varFlotante))   // "float32"
    fmt.Println("typeOf(varBooleano):", typeOf(varBooleano))   // "bool"
    fmt.Println("typeOf(varCaracter):", typeOf(varCaracter))   // "rune"
    fmt.Println("typeOf(varTexto):", typeOf(varTexto))         // "string"
    fmt.Println("typeOf(varArreglo):", typeOf(varArreglo))     // "[3]int32" o "array" (según diseño)

    fmt.Println("\n=== FIN DE CALIFICACION: FUNCIONES EMBEBIDAS ===")
}

/*
=== INICIO DE CALIFICACION: FUNCIONES EMBEBIDAS ===

--- 4.1 FMT.PRINTLN ---
La función fmt.Println permite imprimir múltiples parámetros.
Tipos mixtos: 100 true 3.1416 65

--- 4.2 LEN() ---
La cadena ' Compiladores 2 ' tiene longitud: 14
El arreglo tiene longitud: 5

--- 4.3 NOW() ---
Fecha y hora actual del sistema: 2026-03-06 04:16:49

--- 4.4 SUBSTR() ---
Subcadena 0-11 de ' Universidad San Carlos ': Universidad
Subcadena 12-22 de ' Universidad San Carlos ': San Carlos

--- 4.5 TYPEOF() ---
typeOf(varEntero): int
typeOf(varFlotante): float64
typeOf(varBooleano): bool
typeOf(varCaracter): int32
typeOf(varTexto): string
typeOf(varArreglo): [3]int32

=== FIN DE CALIFICACION: FUNCIONES EMBEBIDAS ===
*/