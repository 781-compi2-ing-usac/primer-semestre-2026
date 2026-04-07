// ====================================================
// ARCHIVO 4 - ARREGLOS MULTIDIMENSIONALES
// Grading: Punto 5 (5.6 - 5.9)
// Cobertura: Funciones de matriz y cubo
// ====================================================

func main() {
	fmt.Println("=== INICIO DE CALIFICACION: ARREGLOS N-D ===")

	// ==========================================
	// 5.6 Funcion de indice de inestabilidad por fila
	// ==========================================
	fmt.Println("\n--- 5.6 INDICE DE INESTABILIDAD ---")
	matrizInstabilidad := [3][4]int32{
		{2, 5, 3, 8},
		{1, 1, 4, 6},
		{7, 3, 9, 9},
	}
	fmt.Println("Indice:", indiceInestabilidad(matrizInstabilidad))

	// ==========================================
	// 5.7 Funcion de regla de Cramer (matriz + arreglo)
	// ==========================================
	fmt.Println("\n--- 5.7 REGLA DE CRAMER ---")
	matrizSistema := [2][2]int32{{2, 1}, {1, 3}}
	vectorSistema := [2]int32{5, 6}
	resultadoCramer := reglaCramer(matrizSistema, vectorSistema)
	fmt.Println("x, y:", resultadoCramer[0], resultadoCramer[1])

	// ==========================================
	// 5.8 Funcion promedio de capas (cubo)
	// ==========================================
	fmt.Println("\n--- 5.8 PROMEDIO DE CAPAS ---")
	cubo := [2][2][2]int32{
		{{1, 3}, {5, 7}},
		{{2, 4}, {6, 8}},
	}
	promedios := promedioCapas(cubo)
	fmt.Println("Promedios capa 0:", promedios[0][0], promedios[0][1])
	fmt.Println("Promedios capa 1:", promedios[1][0], promedios[1][1])

	// ==========================================
	// 5.9 Funcion softmax (retorno de matriz)
	// ==========================================
	fmt.Println("\n--- 5.9 SOFTMAX ---")
	matrizSoft := [2][3]float32{{1.0, 2.0, 3.0}, {4.0, 2.0, 1.0}}
	soft := softmax(matrizSoft)
	fmt.Println("Fila 0:", soft[0][0], soft[0][1], soft[0][2])
	fmt.Println("Fila 1:", soft[1][0], soft[1][1], soft[1][2])

	fmt.Println("\n=== FIN DE CALIFICACION: ARREGLOS N-D ===")
}

func indiceInestabilidad(matriz [3][4]int32) int32 {
	var total int32 = 0
	for i := 0; i < 3; i++ {
		for j := 1; j < 4; j++ {
			dif := matriz[i][j] - matriz[i][j-1]
			if dif < 0 {
				dif = -dif
			}
			total += dif
		}
	}
	return total
}

func reglaCramer(matriz [2][2]int32, vector [2]int32) [2]int32 {
	var respuesta [2]int32
	det := matriz[0][0]*matriz[1][1] - matriz[0][1]*matriz[1][0]
	if det == 0 {
		return respuesta
	}
	detX := vector[0]*matriz[1][1] - matriz[0][1]*vector[1]
	detY := matriz[0][0]*vector[1] - vector[0]*matriz[1][0]
	respuesta[0] = detX / det
	respuesta[1] = detY / det
	return respuesta
}

func promedioCapas(cubo [2][2][2]int32) [2][2]float32 {
	var salida [2][2]float32
	for k := 0; k < 2; k++ {
		for i := 0; i < 2; i++ {
			var suma int32 = 0
			for j := 0; j < 2; j++ {
				suma += cubo[k][i][j]
			}
			salida[k][i] = float32(suma) / 2.0
		}
	}
	return salida
}

func softmax(matriz [2][3]float32) [2][3]float32 {
	var salida [2][3]float32
	for i := 0; i < 2; i++ {
		var max float32 = matriz[i][0]
		for j := 1; j < 3; j++ {
			if matriz[i][j] > max {
				max = matriz[i][j]
			}
		}

		var sum float32 = 0.0
		var exp [3]float32
		for j := 0; j < 3; j++ {
			v := 1.0 + (matriz[i][j] - max)
			if v < 0.1 {
				v = 0.1
			}
			exp[j] = v
			sum += v
		}

		for j := 0; j < 3; j++ {
			salida[i][j] = exp[j] / sum
		}
	}
	return salida
}

/*
=== INICIO DE CALIFICACION: ARREGLOS N-D ===

--- 5.6 INDICE DE INESTABILIDAD ---
Indice: 25

--- 5.7 REGLA DE CRAMER ---
x, y: 1 1

--- 5.8 PROMEDIO DE CAPAS ---
Promedios capa 0: 2 6
Promedios capa 1: 3 7

--- 5.9 SOFTMAX ---
Fila 0: 0.083333336 0.083333336 0.8333333
Fila 1: 0.8333333 0.083333336 0.083333336

=== FIN DE CALIFICACION: ARREGLOS N-D ===
*/
