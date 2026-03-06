# Semana 2 - Ejemplo 1

## Descripción General

El intérprete evoluciona hacia un **lenguaje con statements**. Ya no solo evalúa expresiones, sino que puede ejecutar instrucciones como `print`. Se agregan más operadores aritméticos y se reorganiza el proyecto con mejor estructura de archivos.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

- **Statement `print`**: Permite imprimir resultados en consola (`print(expr);`)
- **Operador resta** (`-`): Operador binario de sustracción
- **Operador división** (`/`): Operador binario de división
- **Operador unario negativo** (`-expr`): Para números negativos
- **Múltiples statements**: El programa puede contener varias instrucciones

### Cambios en la estructura del proyecto

- **Carpeta `src/`**: El intérprete ahora está organizado en una carpeta separada
- **`bootstrap.php`**: Archivo de inicialización que configura el autoload de ANTLR4
- **Múltiples reglas etiquetadas**: Uso de `#LabelName` para diferenciar alternativas en la gramática

### Cambios en el intérprete

- **Método `visitProgram`**: Recorre todos los statements del programa
- **Propiedad `$console`**: Acumula la salida de los prints para mostrarla al usuario
- **Operador switch**: Maneja diferentes operadores binarios de forma dinámica

## Estructura del Proyecto

- `Grammar.g4`: Gramática con support para programa, statements y expresiones
- `src/Interpreter.php`: Implementación del visitor con soporte para múltiples operaciones
- `bootstrap.php`: Inicialización del proyecto y autoload de ANTLR4
- `index.php`: Interfaz de usuario para ejecutar el intérprete
- `composer.json`: Configuración de dependencias
- `ANTLRv4/`: Directorio generado con el lexer, parser y visitors (no incluido en el repo)

## Cómo generar el parser

Para generar los archivos del parser a partir de la gramática, ejecuta:

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

**¿Qué hace este comando?**
- Genera el lexer, parser, y clases visitor en PHP
- Los archivos se guardan en `ANTLRv4/`
- El flag `-visitor` genera las interfaces para el patrón Visitor

## Cómo ejecutar el proyecto

Para ejecutar el intérprete, inicia un servidor PHP local:

```bash
php -S 0.0.0.0:8080
```

**¿Qué hace este comando?**
- Levanta un servidor web local en el puerto 8080
- Accede desde `http://localhost:8080` para usar el intérprete

## Conceptos aprendidos en esta semana

- **Statements vs Expresiones**: Los statements ejecutan acciones, las expresiones producen valores
- **Múltiples reglas en un programa**: Cómo estructurar un programa con múltiples instrucciones
- **Operadores unarios vs binarios**: Diferencia en aridad y precedencia
- **Etiquetas en ANTLR4**: Uso de `#Label` para generar métodos visitor específicos
- **Organización de proyectos PHP**: Separación de código en directorios (`src/`)
- **Autoload con Composer**: Carga automática de clases generadas por ANTLR4
