// ====================================================
// ARCHIVO 1 - FUNCIONALIDADES BASICAS
// Grading: Punto 1 (1.1 - 1.15)
// Cobertura: Variables, constantes, tipos, operadores y comentarios
// ====================================================

// 1.9 Comentario de una linea
/*
1.10 Comentario multilinea:
Archivo de prueba con secciones alineadas a la hoja de calificacion.
*/

func main() {
	fmt.Println("=== INICIO DE CALIFICACION: FUNCIONALIDADES BASICAS ===")

	// ==========================================
	// 1.1 Declaracion larga de variables
	// ==========================================
	fmt.Println("\n--- 1.1 DECLARACION LARGA ---")
	var varInt int32 = 42
	var varFloat float32 = 3.14
	var varBool bool = true
	var varRune rune = 'G'
	var varString string = "Golampi"
	fmt.Println(varInt, varFloat, varBool, varRune, varString)

	// ==========================================
	// 1.2 Asignacion de variables
	// ==========================================
	fmt.Println("\n--- 1.2 ASIGNACION DE VARIABLES ---")
	varInt = 120
	varFloat = 9.75
	varBool = false
	varRune = 'Z'
	varString = "Actualizado"
	fmt.Println(varInt, varFloat, varBool, varRune, varString)

	// ==========================================
	// 1.3 Formato de identificadores
	// ==========================================
	fmt.Println("\n--- 1.3 FORMATO DE IDENTIFICADORES ---")
	var identificador int32 = 1
	var Identificador int32 = 2
	fmt.Println("Case sensitive:", identificador, Identificador)

	// ==========================================
	// 1.4 Declaracion corta de variables
	// ==========================================
	fmt.Println("\n--- 1.4 DECLARACION CORTA ---")
	cInt := 7
	cFloat := 2.5
	cBool := true
	cRune := 'X'
	cString := "Inferencia"
	fmt.Println(cInt, cFloat, cBool, cRune, cString)

	// ==========================================
	// 1.5 Declaracion larga sin inicializar
	// ==========================================
	fmt.Println("\n--- 1.5 DECLARACION LARGA SIN INICIALIZAR ---")
	var defInt int32
	var defFloat float32
	var defBool bool
	var defRune rune
	var defString string
	fmt.Println(defInt, defFloat, defBool, defRune, defString)

	// ==========================================
	// 1.6 Declaracion multiple (larga y corta)
	// ==========================================
	fmt.Println("\n--- 1.6 DECLARACION MULTIPLE ---")
	var m1, m2 int32 = 10, 20
	m3, m4 := "Hola", "Mundo"
	fmt.Println(m1, m2, m3, m4)

	// ==========================================
	// 1.7 Declaracion de constantes
	// ==========================================
	fmt.Println("\n--- 1.7 CONSTANTES ---")
	const PI float32 = 3.14159
	const MAX int32 = 1000
	fmt.Println(PI, MAX)

	// ==========================================
	// 1.8 Manejo de nil
	// ==========================================
	fmt.Println("\n--- 1.8 MANEJO DE NIL ---")
	fmt.Println("Impresion de nil:", nil)
	fmt.Println("Comparacion nil == nil:", nil == nil)

	// ==========================================
	// 1.11 Operaciones aritmeticas
	// ==========================================
	fmt.Println("\n--- 1.11 OPERACIONES ARITMETICAS ---")
	fmt.Println("+:", 15+25)
	fmt.Println("-:", 50-18)
	fmt.Println("*:", 7*8)
	fmt.Println("/:", 100/3)
	fmt.Println("%:", 17%5)

	// ==========================================
	// 1.12 Operaciones relacionales
	// ==========================================
	fmt.Println("\n--- 1.12 OPERACIONES RELACIONALES ---")
	var r1 int32 = 10
	var r2 int32 = 20
	fmt.Println("==:", r1 == r2)
	fmt.Println("!=:", r1 != r2)
	fmt.Println("<:", r1 < r2)
	fmt.Println(">:", r1 > r2)

	// ==========================================
	// 1.13 Operaciones logicas
	// ==========================================
	fmt.Println("\n--- 1.13 OPERACIONES LOGICAS ---")
	var t bool = true
	var f bool = false
	fmt.Println("true && false:", t && f)
	fmt.Println("true || false:", t || f)
	fmt.Println("!true:", !t)

	// ==========================================
	// 1.14 Restriccion de corto circuito
	// ==========================================
	fmt.Println("\n--- 1.14 CORTO CIRCUITO ---")
	var divisor int32 = 0
	ccAnd := false && (100/divisor == 1)
	ccOr := true || (100/divisor == 1)
	fmt.Println("AND:", ccAnd)
	fmt.Println("OR:", ccOr)

	// ==========================================
	// 1.15 Operadores de asignacion
	// ==========================================
	fmt.Println("\n--- 1.15 OPERADORES DE ASIGNACION ---")
	var asig int32 = 50
	asig += 10
	asig -= 5
	asig *= 2
	asig /= 5
	fmt.Println("Resultado final:", asig)

	fmt.Println("\n=== FIN DE CALIFICACION: FUNCIONALIDADES BASICAS ===")
}

/*
=== INICIO DE CALIFICACION: FUNCIONALIDADES BASICAS ===

--- 1.1 DECLARACION LARGA ---
42 3.14 true 71 Golampi

--- 1.2 ASIGNACION DE VARIABLES ---
120 9.75 false 90 Actualizado

--- 1.3 FORMATO DE IDENTIFICADORES ---
Case sensitive: 1 2

--- 1.4 DECLARACION CORTA ---
7 2.5 true 88 Inferencia

--- 1.5 DECLARACION LARGA SIN INICIALIZAR ---
0 0 false 0 

--- 1.6 DECLARACION MULTIPLE ---
10 20 Hola Mundo

--- 1.7 CONSTANTES ---
3.14159 1000

--- 1.8 MANEJO DE NIL ---
Impresion de nil: <nil>
Comparacion nil == nil: <nil>

--- 1.11 OPERACIONES ARITMETICAS ---
+: 40
-: 32
*: 56
/: 33
%: 2

--- 1.12 OPERACIONES RELACIONALES ---
==: false
!=: true
<: true
>: false

--- 1.13 OPERACIONES LOGICAS ---
true && false: false
true || false: true
!true: false

--- 1.14 CORTO CIRCUITO ---
AND: false
OR: true

--- 1.15 OPERADORES DE ASIGNACION ---
Resultado final: 22

=== FIN DE CALIFICACION: FUNCIONALIDADES BASICAS ===
*/
