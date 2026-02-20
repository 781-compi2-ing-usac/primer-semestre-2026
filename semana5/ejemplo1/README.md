# Semana 5 - Ejemplo 1

## Descripción General

El intérprete alcanza una etapa avanzada con la implementación de **funciones**. Ahora es posible definir subrutinas reutilizables con parámetros, invocarlas, y retornar valores. Se introduce el concepto de **closures** y **stack de llamadas**.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

- **Declaración de funciones** (`func ID (params?) block`): Define funciones con nombre, parámetros y cuerpo
- **Llamado a funciones como statement** (`ID(args?)`): Ejecuta una función y descarta su resultado
- **Llamado a funciones como expresión** (`ID(args?)`): Usa el valor retornado por la función
- **Statement `return`** (`return expr?`): Retorna un valor desde una función
- **Lista de parámetros** (`params: ID (',' ID)*`): Parámetros formales de la función
- **Lista de argumentos** (`args: e (',' e)*`): Valores pasados al llamar la función

### Nuevas clases

#### `Invocable.php`

Interfaz base para objetos llamables (funciones):

- **`get_arity()`**: Retorna el número de parámetros esperados
- **`invoke($visitor, $args)`**: Ejecuta la función con los argumentos dados

#### `Foreigns.php`

Implementa funciones definidas por el usuario (Foreign Functions):

- **`$ctx`**: Contexto AST del cuerpo de la función
- **`$closure`**: Entorno donde se declaró la función (captura de variables)
- **`$params`**: Lista de nombres de parámetros
- **`invoke()`**: 
  - Crea un nuevo entorno hijo del closure
  - Vincula argumentos con parámetros
  - Ejecuta el cuerpo de la función
  - Maneja el `ReturnType` para retornar valores
  - Restaura el entorno después de la ejecución

#### `Natives.php`

Preparación para funciones nativas (implementadas en PHP):

- Permite extender el lenguaje con funciones predefinidas

### Extensión de `FlowTypes.php`

- **`ReturnType`**: Ahora almacena el valor a retornar (`$value`)
- Se utiliza para propagar el valor de retorno desde cualquier profundidad en la función

### Cambios en el intérprete

- **Tabla de funciones**: Las funciones se almacenan en el entorno como objetos `Invocable`
- **Verificación de aridad**: Valida que el número de argumentos coincida con los parámetros
- **Ejecución de funciones**: Llama al método `invoke()` del objeto función
- **Manejo de returns**: Captura `ReturnType` y extrae su valor
- **Closures**: Las funciones capturan el entorno en el que fueron declaradas

## Estructura del Proyecto

- `Grammar.g4`: Gramática extendida con funciones, return, params, args
- `src/Interpreter.php`: Visitor con soporte para declaración e invocación de funciones
- `src/Environment.php`: Manejo de entornos (sin cambios)
- `src/FlowTypes.php`: BreakType, ContinueType, ReturnType
- `src/Invocable.php`: Interfaz para objetos llamables
- `src/Foreigns.php`: Implementación de funciones definidas por el usuario
- `src/Natives.php`: Base para funciones nativas
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
- Genera el parser con soporte para funciones
- Crea los métodos visitor correspondientes a declaración y llamado de funciones

## Cómo ejecutar el proyecto

Para ejecutar el intérprete, inicia un servidor PHP local:

```bash
php -S 0.0.0.0:8080
```

**¿Qué hace este comando?**
- Inicia el servidor con la interfaz web
- Permite definir y ejecutar funciones

## Conceptos aprendidos en esta semana

- **Funciones**: Subrutinas reutilizables con parámetros y valores de retorno
- **Declaración vs Invocación**: Definir una función vs ejecutarla
- **Parámetros formales vs Argumentos**: Parámetros en la definición, argumentos en la llamada
- **Aridad**: Número de parámetros que acepta una función
- **Return statement**: Finaliza la ejecución de una función y retorna un valor
- **Closures (Clausuras)**: Captura del entorno donde se declara la función
- **Alcance léxico**: Las funciones se evalúan en su entorno de definición, no de invocación
- **Stack de llamadas**: Pila de entornos que se crea al invocar funciones
- **First-class functions**: Las funciones se tratan como valores (se almacenan en variables)
- **Objetos invocables**: Abstracción mediante la interfaz `Invocable`
- **Foreign vs Native functions**: Funciones del lenguaje vs funciones implementadas en el host
