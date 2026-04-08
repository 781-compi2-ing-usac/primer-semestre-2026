// ====================================================
// ARCHIVO 3 - ARREGLOS (1D Y MULTIDIMENSIONAL BASICO)
// Grading: Punto 5 (5.1 - 5.5)
// Cobertura: Declaracion, acceso y modificacion de arreglos
// ====================================================

func main() {
	fmt.Println("=== INICIO DE CALIFICACION: ARREGLOS ===")

	// ==========================================
	// 5.1 Declaracion no inicializada e inicializada
	// ==========================================
	fmt.Println("\n--- 5.1 DECLARACION 1D INICIALIZADA Y NO INICIALIZADA ---")
	var arregloNoInit [5]int32
	arregloInit := [5]int32{10, 20, 30, 40, 50}
	fmt.Println("No inicializado pos0:", arregloNoInit[0])
	fmt.Println("Inicializado pos2:", arregloInit[2])

	// ==========================================
	// 5.2 Arreglos de tipos estaticos
	// ==========================================
	fmt.Println("\n--- 5.2 ARREGLOS DE TIPOS ESTATICOS ---")
	arrFloat := [3]float32{1.1, 2.2, 3.3}
	arrBool := [3]bool{true, false, true}
	arrRune := [3]rune{'A', 'B', 'C'}
	arrString := [3]string{"Ana", "Luis", "Maria"}
	fmt.Println(arrFloat[0], arrBool[1], arrRune[2], arrString[0])

	// ==========================================
	// 5.3 Acceso y modificacion de elementos
	// ==========================================
	fmt.Println("\n--- 5.3 ACCESO Y MODIFICACION 1D ---")
	fmt.Println("Original arregloInit[1]:", arregloInit[1])
	arregloInit[1] = 99
	fmt.Println("Modificado arregloInit[1]:", arregloInit[1])
	fmt.Println("Longitud arregloInit:", len(arregloInit))

	// ==========================================
	// 5.4 Declaracion multidimensional inicializada y no inicializada
	// ==========================================
	fmt.Println("\n--- 5.4 DECLARACION MULTIDIMENSIONAL ---")
	var matrizNoInit [2][2]int32
	matrizInit := [2][2]int32{{1, 2}, {3, 4}}
	fmt.Println("Matriz no inicializada [1][1]:", matrizNoInit[1][1])
	fmt.Println("Matriz inicializada [0][0]:", matrizInit[0][0])

	// ==========================================
	// 5.5 Acceso y modificacion en arreglo multidimensional
	// ==========================================
	fmt.Println("\n--- 5.5 ACCESO Y MODIFICACION MULTIDIMENSIONAL ---")
	fmt.Println("Original matrizNoInit[0][1]:", matrizNoInit[0][1])
	matrizNoInit[0][1] = 77
	fmt.Println("Modificado matrizNoInit[0][1]:", matrizNoInit[0][1])

	fmt.Println("\n=== FIN DE CALIFICACION: ARREGLOS ===")
}

/*
=== INICIO DE CALIFICACION: ARREGLOS ===

--- 5.1 DECLARACION 1D INICIALIZADA Y NO INICIALIZADA ---
No inicializado pos0: 0
Inicializado pos2: 30

--- 5.2 ARREGLOS DE TIPOS ESTATICOS ---
1.1 false 67 Ana

--- 5.3 ACCESO Y MODIFICACION 1D ---
Original arregloInit[1]: 20
Modificado arregloInit[1]: 99
Longitud arregloInit: 5

--- 5.4 DECLARACION MULTIDIMENSIONAL ---
Matriz no inicializada [1][1]: 0
Matriz inicializada [0][0]: 1

--- 5.5 ACCESO Y MODIFICACION MULTIDIMENSIONAL ---
Original matrizNoInit[0][1]: 0
Modificado matrizNoInit[0][1]: 77

=== FIN DE CALIFICACION: ARREGLOS ===
*/
