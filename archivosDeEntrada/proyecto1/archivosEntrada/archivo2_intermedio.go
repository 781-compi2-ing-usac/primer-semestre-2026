// ====================================================
// ARCHIVO 2 - FUNCIONALIDADES INTERMEDIAS
// Grading: Punto 2 (2.2 - 2.9)
// Cobertura: if, if-else, switch, for, break, continue
// ====================================================

func main() {
	fmt.Println("=== INICIO DE CALIFICACION: ESTRUCTURAS DE CONTROL ===")

	nota1 := 85
	nota2 := 92
	estudiante := "Ana"

	// ==========================================
	// 2.2 if
	// ==========================================
	fmt.Println("\n--- 2.2 IF ---")
	if nota1 > 80 {
		fmt.Println("El estudiante", estudiante, "tiene una nota mayor a 80")
	}

	// ==========================================
	// 2.3 if else
	// ==========================================
	fmt.Println("\n--- 2.3 IF ELSE ---")
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
	// 2.4 switch/case/default
	// ==========================================
	fmt.Println("\n--- 2.4 SWITCH CASE DEFAULT ---")
	codigoCategoria := 2
	switch codigoCategoria {
	case 1:
		fmt.Println("Categoria 1: Principiante")
	case 2:
		fmt.Println("Categoria 2: Intermedio")
	case 3:
		fmt.Println("Categoria 3: Avanzado")
	default:
		fmt.Println("Categoria Desconocida")
	}

	// ==========================================
	// 2.5 for clasico
	// ==========================================
	fmt.Println("\n--- 2.5 FOR CLASICO ---")
	for i := 1; i <= 5; i++ {
		fmt.Println("Iteracion:", i)
	}

	// ==========================================
	// 2.6 for condicional
	// ==========================================
	fmt.Println("\n--- 2.6 FOR CONDICIONAL ---")
	contador := 10
	for contador > 0 {
		fmt.Println("Cuenta regresiva:", contador)
		contador -= 3
	}

	// ==========================================
	// 2.7 for infinito
	// ==========================================
	fmt.Println("\n--- 2.7 FOR INFINITO ---")
	intentos := 0
	for {
		intentos++
		fmt.Println("Intento:", intentos)
		if intentos == 3 {
			break
		}
	}

	// ==========================================
	// 2.8 break
	// ==========================================
	fmt.Println("\n--- 2.8 BREAK ---")
	for i := 1; i <= 20; i++ {
		if i == 7 {
			fmt.Println("Se encontro 7, se aplica break")
			break
		}
		fmt.Println(i)
	}

	// ==========================================
	// 2.9 continue
	// ==========================================
	fmt.Println("\n--- 2.9 CONTINUE ---")
	for j := 1; j <= 6; j++ {
		if j%2 == 0 {
			continue
		}
		fmt.Println("Impar:", j)
	}

	fmt.Println("\n=== FIN DE CALIFICACION: ESTRUCTURAS DE CONTROL ===")
}

/*
=== INICIO DE CALIFICACION: ESTRUCTURAS DE CONTROL ===

--- 2.2 IF ---
El estudiante Ana tiene una nota mayor a 80

--- 2.3 IF ELSE ---
Clasificacion: EXCELENTE

--- 2.4 SWITCH CASE DEFAULT ---
Categoria 2: Intermedio

--- 2.5 FOR CLASICO ---
Iteracion: 1
Iteracion: 2
Iteracion: 3
Iteracion: 4
Iteracion: 5

--- 2.6 FOR CONDICIONAL ---
Cuenta regresiva: 10
Cuenta regresiva: 7
Cuenta regresiva: 4
Cuenta regresiva: 1

--- 2.7 FOR INFINITO ---
Intento: 1
Intento: 2
Intento: 3

--- 2.8 BREAK ---
1
2
3
4
5
6
Se encontro 7, se aplica break

--- 2.9 CONTINUE ---
Impar: 1
Impar: 3
Impar: 5

=== FIN DE CALIFICACION: ESTRUCTURAS DE CONTROL ===
*/
