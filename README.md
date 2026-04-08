# OLC2_1S2026

Bienvenido al repositorio de todas las secciones. Aquí se encuentran los ejercicios, ejemplos y demás recursos utilizados en cada sesión de laboratorio, no dudes utilizar los foros para cualquier pregunta acerca de los recursos de este repositorio. El equipo académico se detalla así:

|Nombre                   |Sección|
|-------------------------|-|
|Diego Felipe Cali Morales|A|
|Rubén Alejandro Ralda Mejia|B|
|Omar Alejandro Vides Esteban|N|


## Contenido
El repositorio está dividido en varias carpetas cada una correspondiente a una semana de contenido:

## Intérprete

- `semana1`: Introducción a los lenguajes de programación y compiladores. Revisamos las herramientas a utilizar durante el curso, configuramos el entorno de desarrollo y demostramos ANTLRv4 como parser. Se implementa una calculadora aritmética simple con suma, multiplicación, paréntesis y números enteros.
- `semana2`: Interpretación de instrucciones básicas. Se implementa un intérprete para la ejecución de statements básicos, agregamos el operador `print`, y más operadores aritméticos (resta, división) así como operadores unarios. Se reorganiza el proyecto con estructura de carpetas.
- `semana3`: Manejo de entornos dentro del intérprete. Se añade soporte para variables, asignaciones, bloques de código y scopes. Se implementa la clase `Environment` para manejar la tabla de símbolos y alcances anidados.
- `semana4`: Estructuras de control. Se implementan valores booleanos, operadores relacionales (==, >, <), condicionales (`if`/`else`), bucles (`while`), y sentencias de transferencia de control (`break`, `continue`). Se utiliza un sistema de `FlowTypes` para manejar el flujo de control.
- `semana5`: Funciones y closures. Se añade soporte para declaración de funciones, parámetros, argumentos y retorno de valores (`return`). Se implementan las clases `Invocable`, `Foreigns` para funciones del usuario y `Natives` para funciones predefinidas.
- `semana6`: Estructuras de datos compuestas. Se implementan arreglos, literales de arreglo, acceso indexado y asignación a posiciones en arreglos, incluyendo acceso anidado multidimensional.

## Compilador

- `semana7`: Inicio de la implementación del compilador. Comenzamos con la generación de código assembler para expresiones y el operador `print`. Este es el punto de transición entre interpretación y compilación.
- `semana8`: 
- `semana9`: 
- `semana10`: 
- `semana11`: 

## Contribuciones
Si deseas contribuir al repositorio puedes hacer uso de GitHub Flow y pronto se estará aprobando tu pull request. De igual manera puedes agregar la pregunta al foro respectivo de tu sección, nos ayudaría mucho.

## Instalación de herramientas

Ubuntu:
pip install antlr4-tools
sudo apt install php composer

Fedora:

## Ejecución rápida

~~~bash
php -S 127.0.0.1:8000
~~~

## Recursos:

* Repositorio de ANTLRv4: https://github.com/antlr/antlr4
* Guia de como crear un intérprete: https://craftinginterpreters.com/
* Documentación de PHP: https://www.php.net/docs.php
* ANTLR4 Runtime for PHP: https://github.com/antlr/antlr4/blob/master/doc/php-target.md
* Getting Started with ANTLR v4: https://github.com/antlr/antlr4/blob/master/doc/getting-started.md
