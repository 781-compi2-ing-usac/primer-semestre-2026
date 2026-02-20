# Semana 4 - Ejemplo 1

## Descripción General

El intérprete incorpora **estructuras de control de flujo**, permitiendo decisiones condicionales y ciclos. Se implementa un mecanismo de **propagación de control** mediante objetos especializados que interrumpen la ejecución normal del programa.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

- **Statement `if`** (`if (expr) block else?`): Ejecución condicional
- **Statement `else`**: Alternativa cuando la condición es falsa
- **Statement `while`** (`while (expr) block`): Ciclo iterativo
- **Statement `break`**: Interrumpe la ejecución de un ciclo
- **Statement `continue`**: Salta a la siguiente iteración de un ciclo
- **Operador de igualdad** (`==`): Comparación de valores
- **Operadores relacionales** (`>`, `<`): Mayor y menor que
- **Valores booleanos** (`true`, `false`): Literales booleanos

### Nueva clase: `FlowTypes.php`

Implementa el **control de flujo mediante objetos**:

- **`FlowType`**: Clase base para tipos de control de flujo
- **`BreakType`**: Indica que se debe salir del ciclo actual
- **`ContinueType`**: Indica que se debe saltar a la siguiente iteración
- **`ReturnType`**: (preparación para funciones) Indica retorno de valor

Estos objetos se propagan hacia arriba en la pila de ejecución hasta encontrar el contexto apropiado que los maneje.

### Cambios en el intérprete

- **Evaluación de expresiones booleanas**: El resultado puede ser `true` o `false`
- **Manejo de `if`**: Evalúa la condición y ejecuta el bloque correspondiente
- **Manejo de `while`**: Repite el bloque mientras la condición sea verdadera
- **Detección de FlowTypes**: En ciclos, detecta `break` y `continue` para interrumpir o continuar
- **Validación de tipos**: Los operadores relacionales y de igualdad retornan booleanos

### Jerarquía de precedencia de operadores

1. **Igualdad**: `==`
2. **Relacionales**: `>`, `<`
3. **Aditivos**: `+`, `-`
4. **Multiplicativos**: `*`, `/`
5. **Unarios**: `-expr`
6. **Primarios**: números, variables, booleanos, paréntesis

## Estructura del Proyecto

- `Grammar.g4`: Gramática con control de flujo y operadores relacionales
- `src/Interpreter.php`: Visitor con soporte para if, while, break, continue
- `src/Environment.php`: Manejo de entornos (sin cambios)
- `src/FlowTypes.php`: Clases para propagación de control de flujo
- `bootstrap.php`: Configuración del proyecto
- `index.php`: Interfaz de ejecución
- `static/`: Interfaz web
- `ANTLRv4/`: Parser generado
- `vendor/`: Dependencias

## Cómo generar el parser

Para generar los archivos del parser a partir de la gramática, ejecuta:

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

**¿Qué hace este comando?**
- Genera el parser con las reglas de control de flujo
- Crea los métodos visitor para if, while, break, continue

## Cómo ejecutar el proyecto

Para ejecutar el intérprete, inicia un servidor PHP local:

```bash
php -S 0.0.0.0:8080
```

**¿Qué hace este comando?**
- Inicia el servidor para la interfaz web
- Permite escribir programas con ciclos y condicionales

## Conceptos aprendidos en esta semana

- **Control de flujo**: Alteración del orden de ejecución del programa
- **Condicionales (if/else)**: Ejecución selectiva basada en condiciones
- **Ciclos (while)**: Repetición de código mientras se cumpla una condición
- **Break y Continue**: Transferencia de control dentro de ciclos
- **Expresiones booleanas**: Evaluación a verdadero o falso
- **Operadores relacionales**: Comparación de valores numéricos
- **Operador de igualdad**: Comparación de valores
- **Propagación de excepciones de flujo**: Uso de objetos especiales para interrumpir la ejecución
- **Cortocircuito de evaluación**: Detener la ejecución cuando se encuentra un FlowType
- **Anidamiento de estructuras**: Ciclos y condicionales dentro de otros
