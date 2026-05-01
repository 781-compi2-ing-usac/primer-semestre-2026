// ====================================================
// ARCHIVO 5 - FUNCIONES
// Grading: Punto 3 (3.1 - 3.7) y Punto 4 (4.1 - 4.5)
// Cobertura: Funciones requeridas y funciones embebidas
// ====================================================

func main() {
	fmt.Println("=== INICIO DE CALIFICACION: FUNCIONES ===")

	// ==========================================
	// 3.1 Funcion no recursiva sin parametros
	// ==========================================
	fmt.Println("\n--- 3.1 IMPRIMIR ARBOL ---")
	imprimirArbol()

	// ==========================================
	// 3.2 Funcion con parametros no recursiva
	// ==========================================
	fmt.Println("\n--- 3.2 CALCULAR VOLUMEN PIRAMIDE ---")
	fmt.Println("Volumen:", calcularVolumenPiramide(10.0, 15.0))

	// ==========================================
	// 3.3 Funciones por referencia
	// ==========================================
	fmt.Println("\n--- 3.3 REFERENCIA: ORDENAMIENTO E INTERCAMBIO ---")
	var a int32 = 100
	var b int32 = 200
	intercambioValores(&a, &b)
	fmt.Println("Intercambio:", a, b)

	var arr [5]int32 = [5]int32{64, 25, 12, 22, 11}
	ordenamientoSeleccion(&arr)
	fmt.Println("Ordenado:", arr[0], arr[1], arr[2], arr[3], arr[4])

	// ==========================================
	// 3.4 Funcion recursiva con un retorno
	// ==========================================
	fmt.Println("\n--- 3.4 POTENCIA RECURSIVA ---")
	fmt.Println("2^8:", potencia(2, 8))

	// ==========================================
	// 3.5 Funciones por referencia (nivel intermedio)
	// ==========================================
	fmt.Println("\n--- 3.5 REFERENCIA AVANZADA: INTERCAMBIO VALIDADO E INTERCALACION ---")
	var x int32 = 50
	var y int32 = 30
	intercambioValoresValidado(&x, &y)
	fmt.Println("Intercambio validado:", x, y)

	var arr2 [6]int32 = [6]int32{64, 34, 25, 12, 22, 11}
	intercalacion(&arr2)
	fmt.Println("Intercalación:", arr2[0], arr2[1], arr2[2], arr2[3], arr2[4], arr2[5])

	// ==========================================
	// 3.6 Funcion recursiva con multiple retorno (Euclides)
	// ==========================================
	fmt.Println("\n--- 3.6 EUCLIDES RECURSIVO ---")
	mcd, pasos := euclides(48, 18)
	fmt.Println("MCD:", mcd, "Pasos:", pasos)

	// ==========================================
	// 4.1 fmt.Println()
	// ==========================================
	fmt.Println("\n--- 4.1 FMT.PRINTLN ---")
	fmt.Println("Impresion directa de texto")

	// ==========================================
	// 4.2 len()
	// ==========================================
	fmt.Println("\n--- 4.2 LEN ---")
	texto := "Golampi"
	fmt.Println("len(texto):", len(texto))
	arrLen := [4]int32{1, 2, 3, 4}
	fmt.Println("len(arrLen):", len(arrLen))

	// ==========================================
	// 4.3 now()
	// ==========================================
	fmt.Println("\n--- 4.3 NOW ---")
	fmt.Println("Fecha actual:", now())

	// ==========================================
	// 4.4 substr()
	// ==========================================
	fmt.Println("\n--- 4.4 SUBSTR ---")
	fmt.Println("substr:", substr("Organizacion de Lenguajes", 0, 12))

	// ==========================================
	// 4.5 typeOf()
	// ==========================================
	fmt.Println("\n--- 4.5 TYPEOF ---")
	fmt.Println("int32:", typeOf(42))
	fmt.Println("float32:", typeOf(3.14))
	fmt.Println("bool:", typeOf(true))
	fmt.Println("string:", typeOf("texto"))

	fmt.Println("\n=== FIN DE CALIFICACION: FUNCIONES ===")
}

func imprimirArbol() {
	fmt.Println("  *")
	fmt.Println(" ***")
	fmt.Println("*****")
}

func calcularVolumenPiramide(base float32, altura float32) float32 {
	return (base * base * altura) / 3.0
}

func intercambioValores(x *int32, y *int32) {
	temp := x
	x = y
	y = temp
}

func ordenamientoSeleccion(arr *[5]int32) {
	for i := 0; i < 4; i++ {
		min := i
		for j := i + 1; j < 5; j++ {
			if arr[j] < arr[min] {
				min = j
			}
		}
		if min != i {
			temp := arr[i]
			arr[i] = arr[min]
			arr[min] = temp
		}
	}
}

func potencia(base int32, exponente int32) int32 {
	if exponente == 0 {
		return 1
	}
	return base * potencia(base, exponente-1)
}

func euclides(a int32, b int32) (int32, int32) {
	if b == 0 {
		return a, 1
	}
	resultado, pasos := euclides(b, a%b)
	return resultado, pasos + 1
}

func intercambioValoresValidado(x *int32, y *int32) {
	if x != nil && y != nil {
		temp := x
		x = y
		y = temp
	}
}

func intercalacion(arr *[6]int32) {
	for i := 0; i < 5; i++ {
		for j := 0; j < 5-i; j++ {
			if arr[j] > arr[j+1] {
				temp := arr[j]
				arr[j] = arr[j+1]
				arr[j+1] = temp
			}
		}
	}
}

/*
=== INICIO DE CALIFICACION: FUNCIONES ===

--- 3.1 IMPRIMIR ARBOL ---
  *
 ***
*****

--- 3.2 CALCULAR VOLUMEN PIRAMIDE ---
Volumen: 500

--- 3.3 REFERENCIA: ORDENAMIENTO E INTERCAMBIO ---
Intercambio: 200 100
Ordenado: 11 12 22 25 64

--- 3.4 POTENCIA RECURSIVA ---
2^8: 256

--- 3.6 EUCLIDES RECURSIVO ---
MCD: 6 Pasos: 4

--- 3.5 REFERENCIA AVANZADA: INTERCAMBIO VALIDADO E INTERCALACION ---
Intercambio validado: 30 50
Intercalación: 11 12 22 25 34 64

--- 4.1 FMT.PRINTLN ---
Impresion directa de texto

--- 4.2 LEN ---
len(texto): 7
len(arrLen): 4

--- 4.3 NOW ---
Fecha actual: <NOW>

--- 4.4 SUBSTR ---
substr: Organizacion

--- 4.5 TYPEOF ---
int32: int32
float32: float32
bool: bool
string: string

=== FIN DE CALIFICACION: FUNCIONES ===
*/