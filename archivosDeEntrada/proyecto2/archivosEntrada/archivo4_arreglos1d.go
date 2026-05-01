// ====================================================
// ARCHIVO 3 - ARREGLOS MULTIDIMENSIONALES
// Grading: Punto 5 (5.1 - 5.2)
// Cobertura: Declaracion, acceso y modificacion de arreglos multidimensionales
// ====================================================

func main() {
	fmt.Println("=== INICIO DE CALIFICACION: ARREGLOS ===")

	// ==========================================
	// 5.1 Declaracion multidimensional inicializada y no inicializada
	// ==========================================
	fmt.Println("\n--- 5.1 DECLARACION MULTIDIMENSIONAL ---")
	var matrizNoInit [2][2]int32
	matrizInit := [2][2]int32{{1, 2}, {3, 4}}
	fmt.Println("Matriz no inicializada [1][1]:", matrizNoInit[1][1])
	fmt.Println("Matriz inicializada [0][0]:", matrizInit[0][0])

	// ==========================================
	// 5.2 Acceso y modificacion en arreglo multidimensional
	// ==========================================
	fmt.Println("\n--- 5.2 ACCESO Y MODIFICACION MULTIDIMENSIONAL ---")
	fmt.Println("Original matrizNoInit[0][1]:", matrizNoInit[0][1])
	matrizNoInit[0][1] = 77
	fmt.Println("Modificado matrizNoInit[0][1]:", matrizNoInit[0][1])

	fmt.Println("\n=== FIN DE CALIFICACION: ARREGLOS ===")
}

/*
=== INICIO DE CALIFICACION: ARREGLOS ===

--- 5.1 DECLARACION MULTIDIMENSIONAL ---
Matriz no inicializada [1][1]: 0
Matriz inicializada [0][0]: 1

--- 5.2 ACCESO Y MODIFICACION MULTIDIMENSIONAL ---
Original matrizNoInit[0][1]: 0
Modificado matrizNoInit[0][1]: 77

=== FIN DE CALIFICACION: ARREGLOS ===
*/
