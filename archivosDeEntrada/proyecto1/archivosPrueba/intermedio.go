func main() {
    fmt.Println("=== INICIO DE CALIFICACION: ESTRUCTURAS DE CONTROL ===")

    nota1 := 85
    nota2 := 92
    estudiante := "Ana"

    // ==========================================
    // 2.2 if
    // ==========================================
    fmt.Println("\n--- 2.2 IF SIMPLE ---")
    if nota1 > 80 {
        fmt.Println("El estudiante", estudiante, "tiene una nota mayor a 80.")
    }

    // ==========================================
    // 2.3 if else (y else if)
    // ==========================================
    fmt.Println("\n--- 2.3 IF ELSE Y ELSE IF ---")
    if nota2 >= 95 {
        fmt.Println("Clasificacion: SOBRESALIENTE")
    } else {
        if nota2 >= 90 {
            fmt.Println("Clasificacion: EXCELENTE")
        } else {
            fmt.Println("Clasificacion: REGULAR")
        }
    }

    // ==========================================
    // 2.4 switch / case / default
    // ==========================================
    fmt.Println("\n--- 2.4 SWITCH / CASE / DEFAULT ---")
    codigoCategoria := 2
    switch codigoCategoria {
    case 1:
        fmt.Println("Categoria 1: Principiante")
    case 2:
        fmt.Println("Categoria 2: Intermedio (Este debe imprimirse)")
    case 3:
        fmt.Println("Categoria 3: Avanzado")
    default:
        fmt.Println("Categoria Desconocida")
    }

    // ==========================================
    // 2.5 for clásico
    // ==========================================
    fmt.Println("\n--- 2.5 FOR CLASICO ---")
    fmt.Println("Contando del 1 al 5:")
    for i := 1; i <= 5; i++ {
        fmt.Println("Iteración clásico:", i)
    }

    // ==========================================
    // 2.6 for condicional (Equivalente al While)
    // ==========================================
    fmt.Println("\n--- 2.6 FOR CONDICIONAL (WHILE) ---")
    contador := 10
    for contador > 0 {
        fmt.Println("Cuenta regresiva:", contador)
        contador -= 3 // Restamos de 3 en 3
    }

    // ==========================================
    // 2.7 for infinito y 2.8 break
    // ==========================================
    fmt.Println("\n--- 2.7 FOR INFINITO Y 2.8 BREAK ---")
    intentos := 0
    for {
        intentos++
        fmt.Println("Ejecutando ciclo infinito, intento número:", intentos)
        
        if intentos == 3 {
            fmt.Println("Límite alcanzado. Ejecutando BREAK para salir del for infinito.")
            break
        }
    }
    fmt.Println("Salió del ciclo infinito exitosamente.")

    // ==========================================
    // 2.9 continue
    // ==========================================
    fmt.Println("\n--- 2.9 CONTINUE ---")
    fmt.Println("Imprimiendo solo números impares del 1 al 6:")
    for j := 1; j <= 6; j++ {
        if j % 2 == 0 {
            fmt.Println("Saltando el número par:", j)
            continue
        }
        fmt.Println("Número impar encontrado y procesado:", j)
    }

    fmt.Println("\n=== FIN DE CALIFICACION: CONTROL DE FLUJO ===")
}

/*
=== INICIO DE CALIFICACION: ESTRUCTURAS DE CONTROL ===

--- 2.2 IF SIMPLE ---
El estudiante Ana tiene una nota mayor a 80.

--- 2.3 IF ELSE Y ELSE IF ---
Clasificacion: EXCELENTE

--- 2.4 SWITCH / CASE / DEFAULT ---
Categoria 2: Intermedio (Este debe imprimirse)

--- 2.5 FOR CLASICO ---
Contando del 1 al 5:
Iteración clásico: 1
Iteración clásico: 2
Iteración clásico: 3
Iteración clásico: 4
Iteración clásico: 5

--- 2.6 FOR CONDICIONAL (WHILE) ---
Cuenta regresiva: 10
Cuenta regresiva: 7
Cuenta regresiva: 4
Cuenta regresiva: 1

--- 2.7 FOR INFINITO Y 2.8 BREAK ---
Ejecutando ciclo infinito, intento número: 1
Ejecutando ciclo infinito, intento número: 2
Ejecutando ciclo infinito, intento número: 3
Límite alcanzado. Ejecutando BREAK para salir del for infinito.
Salió del ciclo infinito exitosamente.

--- 2.9 CONTINUE ---
Imprimiendo solo números impares del 1 al 6:
Número impar encontrado y procesado: 1
Saltando el número par: 2
Número impar encontrado y procesado: 3
Saltando el número par: 4
Número impar encontrado y procesado: 5
Saltando el número par: 6

=== FIN DE CALIFICACION: CONTROL DE FLUJO ===
*/