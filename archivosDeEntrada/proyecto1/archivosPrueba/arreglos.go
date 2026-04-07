func main() {
    fmt.Println("=== INICIO DE CALIFICACION: ARREGLOS Y MATRICES ===")
    // ==========================================
    // 5.1 Declaración no inicializada e inicializada
    // 5.2 Arreglos de tipos estáticos
    // ==========================================
    fmt.Println("\n--- 5.1 y 5.2 DECLARACION DE ARREGLOS 1D (TIPOS ESTATICOS) ---")
    
    // Declaración NO inicializada (deben tener valores por defecto: 0, "", false)
    var arregloEnteros [5]int32
    var arregloCadenas [3]string
    
    // Declaración Inicializada
    arregloPrecios := [4]float32{150.5, 200.0, 99.99, 350.75}
    arregloBooleanos := [2]bool{true, false}

    fmt.Println("Arreglo no inicializado (int32) en pos 0:", arregloEnteros[0])
    fmt.Println("Arreglo inicializado (float32) en pos 2:", arregloPrecios[2])
    fmt.Println("Arreglo inicializado (bool) en pos 2:", arregloBooleanos[1])

    // ==========================================
    // 5.3 Acceso y Modificación de elementos
    // ==========================================
    fmt.Println("\n--- 5.3 ACCESO Y MODIFICACION DE ELEMENTOS (1D) ---")
    fmt.Println("Valor original arregloEnteros[2]:", arregloEnteros[2])
    
    // Modificación
    arregloEnteros[2] = 1024
    arregloCadenas[1] = "Golampi"
    
    // Acceso
    fmt.Println("Nuevo valor arregloEnteros[2]:", arregloEnteros[2])
    fmt.Println("Valor modificado arregloCadenas[1]:", arregloCadenas[1])

    // ==========================================
    // 5.4 Declaración multidimensional inicializada y no inicializada
    // ==========================================
    fmt.Println("\n--- 5.4 DECLARACION MULTIDIMENSIONAL ---")
    
    // NO inicializada (Matriz 3x3 de enteros llena de ceros)
    var matrizCeros [3][3]int32
    
    // Inicializada (Matriz 2x2 predefinida)
    matrizIdentidad := [2][2]int32{
        {1, 0},
        {0, 1},
    }

    fmt.Println("Matriz no inicializada [2][2]:", matrizCeros[2][2])
    fmt.Println("Matriz inicializada [0][0] y [1][1]:", matrizIdentidad[0][0], ",", matrizIdentidad[1][1])

    // ==========================================
    // 5.5 Acceso y Modificación de elementos en arreglo multidimensional
    // ==========================================
    fmt.Println("\n--- 5.5 ACCESO Y MODIFICACION MULTIDIMENSIONAL ---")
    fmt.Println("Valor original matrizCeros[1][1]:", matrizCeros[1][1])
    
    // Modificación
    matrizCeros[1][1] = 99
    matrizCeros[0][2] = 50
    
    // Acceso
    fmt.Println("Nuevo valor matrizCeros[1][1]:", matrizCeros[1][1])
    fmt.Println("Nuevo valor matrizCeros[0][2]:", matrizCeros[0][2])

    // ==========================================
    // 5.7 Función de Matriz + Arreglo
    // ==========================================
    fmt.Println("\n--- 5.7 FUNCION QUE RECIBE MATRIZ Y ARREGLO ---")
    // Pasamos el arreglo y la matriz que ya tenemos inicializados
    procesarMatrizYArreglo(matrizIdentidad, arregloPrecios)

    // ==========================================
    // 5.9 Función con retorno de matriz
    // ==========================================
    fmt.Println("\n--- 5.9 FUNCION CON RETORNO DE MATRIZ ---")
    matrizGenerada := generarMatrizMagica()
    fmt.Println("Matriz generada retornada, posición [0][1]:", matrizGenerada[0][1])
    fmt.Println("Matriz generada retornada, posición [1][0]:", matrizGenerada[1][0])

    fmt.Println("\n=== FIN DE CALIFICACION: ARREGLOS Y MATRICES ===")
}

// ------------------------------------------------------------------
// FUNCIONES DE APOYO PARA ARREGLOS Y MATRICES
// ------------------------------------------------------------------

// Función para evaluar el punto 5.7
// Recibe una matriz 2x2 de int32 y un arreglo de 4 float32
func procesarMatrizYArreglo(mat [2][2]int32, arr [4]float32) {
    fmt.Println(">>> Dentro de procesarMatrizYArreglo")
    fmt.Println("Recibido elemento de Matriz [0][0]:", mat[0][0])
    fmt.Println("Recibido elemento de Arreglo [3]:", arr[3])
}

// Función para evaluar el punto 5.9
// Retorna una matriz estática de 2x2
func generarMatrizMagica() [2][2]int32 {
    fmt.Println(">>> Dentro de generarMatrizMagica")
    nuevaMatriz := [2][2]int32{
        {8, 1},
        {3, 5},
    }
    return nuevaMatriz
}

/*
=== INICIO DE CALIFICACION: ARREGLOS Y MATRICES ===

--- 5.1 y 5.2 DECLARACION DE ARREGLOS 1D (TIPOS ESTATICOS) ---
Arreglo no inicializado (int32) en pos 0: 0
Arreglo inicializado (float32) en pos 2: 99.99
Arreglo inicializado (bool) en pos 2: false

--- 5.3 ACCESO Y MODIFICACION DE ELEMENTOS (1D) ---
Valor original arregloEnteros[2]: 0
Nuevo valor arregloEnteros[2]: 1024
Valor modificado arregloCadenas[1]: Golampi

--- 5.4 DECLARACION MULTIDIMENSIONAL ---
Matriz no inicializada [2][2]: 0
Matriz inicializada [0][0] y [1][1]: 1 , 1

--- 5.5 ACCESO Y MODIFICACION MULTIDIMENSIONAL ---
Valor original matrizCeros[1][1]: 0
Nuevo valor matrizCeros[1][1]: 99
Nuevo valor matrizCeros[0][2]: 50

--- 5.7 FUNCION QUE RECIBE MATRIZ Y ARREGLO ---
>>> Dentro de procesarMatrizYArreglo
Recibido elemento de Matriz [0][0]: 1
Recibido elemento de Arreglo [3]: 350.75

--- 5.9 FUNCION CON RETORNO DE MATRIZ ---
>>> Dentro de generarMatrizMagica
Matriz generada retornada, posición [0][1]: 1
Matriz generada retornada, posición [1][0]: 3

=== FIN DE CALIFICACION: ARREGLOS Y MATRICES ===
*/