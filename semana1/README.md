# Semana 1 - Ejemplo 1

## Descripción General

Este es el primer ejemplo de un intérprete básico construido con ANTLR4 y PHP. El objetivo es crear una **calculadora aritmética simple** que evalúa expresiones con suma y multiplicación, respetando la precedencia de operadores y el uso de paréntesis.

## Estructura del Proyecto

- `Grammar.g4`: Gramática ANTLR4 que define las reglas de suma, multiplicación y expresiones con paréntesis
- `Interpreter.php`: Implementación del visitor que evalúa las expresiones y retorna resultados numéricos
- `index.php`: Punto de entrada que inicializa el lexer, parser e intérprete
- `composer.json`: Dependencias del proyecto (antlr4-runtime)

## Características

Este intérprete soporta:

- **Suma** (`+`): Operador binario de adición
- **Multiplicación** (`*`): Operador binario de multiplicación
- **Paréntesis** (`(` `)`): Para agrupar expresiones y modificar precedencia
- **Números enteros**: Expresados como dígitos (`[0-9]+`)

### Ejemplos de expresiones válidas

```
5 + 3
2 * 4
5 + 3 * 2
(5 + 3) * 2
```

## Cómo generar el parser

Para generar los archivos del parser a partir de la gramática, ejecuta:

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

**¿Qué hace este comando?**
- `antlr4`: Herramienta generadora de parsers
- `-Dlanguage=PHP`: Genera código en PHP
- `Grammar.g4`: Archivo de entrada con la gramática
- `-visitor`: Genera el patrón Visitor (usado en el intérprete)
- `-o ANTLRv4/`: Directorio de salida para los archivos generados

## Cómo ejecutar el proyecto

Para ejecutar el intérprete, inicia un servidor PHP local:

```bash
php -S 0.0.0.0:8080
```

**¿Qué hace este comando?**
- Inicia un servidor web en el puerto 8080
- Puedes acceder desde el navegador en `http://localhost:8080`
- El servidor ejecuta `index.php` que procesa las expresiones

## Conceptos aprendidos en esta semana

- **Gramática BNF/EBNF**: Definición formal de un lenguaje mediante reglas de producción
- **Lexer y Parser**: Diferencia entre análisis léxico (tokens) y análisis sintáctico (árbol)
- **Árbol de Sintaxis Abstracta (AST)**: Representación jerárquica del código
- **Patrón Visitor**: Recorrido del AST para evaluación
- **Precedencia de operadores**: La multiplicación tiene mayor precedencia que la suma
- **Asociatividad**: Ambos operadores son asociativos a la izquierda
- **ANTLR4**: Herramienta para generar parsers a partir de gramáticas declarativas

En esta semana aprenderemos qué es ANTLR, cómo configurar el entorno de desarrollo y realizaremos una demostración de ANTLR como parser.

## Requisitos:

- Usaremos linux en el transcurso del curso.
- Instalaremos las siguientes herramientas:

```bash
pip install antlr4-tools
sudo apt install php composer
```

- Además de configurar lo siguiente:

```bash
composer require antlr/antlr4-php-runtime
```

- Debemos definir nuestra grammar en un archivo, en este caso `Grammar.g4`.
- Ejecutaremos el siguiente comando para generar el parser y la interfaz del visitante:

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor
```

## Ejecución

Varias opciones

1. Utilizando un servidor apache configurado con php: Realizando una instalación desde cero en la maquina o utilizando servicios como XAMPP.
2. Utilizando un servidor configurado en una imagen de docker.
3. Instalando php y utilizar el light server que ofrece.

En ambientes de locales de desarrollo utilizaremos la tercera opción. El comando es:
~~~bash
php -S 127.0.0.1:8000
~~~