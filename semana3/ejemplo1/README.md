# Semana 3 - Ejemplo 1

## Descripción General

El intérprete ahora soporta **variables** y **entornos de ejecución**. Esto permite almacenar valores en memoria, referenciarlos y modificarlos. Se introduce el concepto de **scope (alcance)** mediante entornos anidados.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

- **Declaración de variables** (`var ID = expr`): Crea una nueva variable en el entorno actual
- **Asignación de variables** (`ID = expr`): Modifica el valor de una variable ya existente
- **Referencia a variables** (`ID`): Usa el valor almacenado en una variable
- **Bloques de código** (`{ stmt* }`): Agrupa múltiples statements

### Nueva clase: `Environment.php`

Esta clase implementa el **entorno de ejecución** y maneja:

- **Almacenamiento de variables**: Tabla de símbolos local
- **Entornos anidados**: Cada entorno puede tener un padre (closure)
- **Búsqueda de variables**: Si no se encuentra localmente, busca en el padre
- **Métodos principales**:
  - `set($key, $value)`: Declara una variable en el entorno actual
  - `get($key)`: Obtiene el valor de una variable (busca en padres si no existe)
  - `assign($key, $value)`: Modifica una variable existente

### Cambios en el intérprete

- **Propiedad `$env`**: Mantiene el entorno actual durante la ejecución
- **Manejo de bloques**: Crea un nuevo entorno hijo para cada bloque
- **Validación de variables**: Error si se intenta leer o asignar una variable no declarada

### Interfaz web

- **Carpeta `static/`**: Contiene archivos CSS y JavaScript para una interfaz más completa
- Mejor presentación del código y resultados

## Estructura del Proyecto

- `Grammar.g4`: Gramática extendida con variables, asignación y bloques
- `src/Interpreter.php`: Visitor con soporte para entornos
- `src/Environment.php`: Implementación de la tabla de símbolos y scopes
- `bootstrap.php`: Configuración inicial del proyecto
- `index.php`: Interfaz web para ejecutar código
- `static/`: Archivos CSS y JS para la interfaz
- `ANTLRv4/`: Parser y lexer generados
- `vendor/`: Dependencias de Composer

## Cómo generar el parser

Para generar los archivos del parser a partir de la gramática, ejecuta:

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

**¿Qué hace este comando?**
- Genera el parser con las nuevas reglas de variables y bloques
- Crea los métodos visitor correspondientes a cada alternativa etiquetada

## Cómo ejecutar el proyecto

Para ejecutar el intérprete, inicia un servidor PHP local:

```bash
php -S 0.0.0.0:8080
```

**¿Qué hace este comando?**
- Levanta un servidor web que sirve la interfaz gráfica
- Permite escribir y ejecutar código con variables

## Conceptos aprendidos en esta semana

- **Variables**: Almacenamiento de valores en memoria con identificadores
- **Entorno de ejecución (Environment)**: Estructura de datos para manejar el scope
- **Scope (Alcance)**: Región del código donde una variable es visible
- **Entornos anidados**: Jerarquía de scopes (local → padre → global)
- **Shadowing**: Variables locales pueden ocultar variables del padre con el mismo nombre
- **Bloques de código**: Agrupación de statements con su propio scope
- **Tabla de símbolos**: Estructura para asociar identificadores con valores
- **Diferencia entre declaración y asignación**: `var` crea, `=` modifica
