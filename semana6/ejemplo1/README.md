# Semana 6 - Ejemplo 1

## Descripción General

En esta semana el intérprete evoluciona de un lenguaje con **variables, control de flujo y funciones** a uno con **soporte de arreglos** (literales, acceso indexado y asignación sobre posiciones).  
El hito conceptual es incorporar **estructuras de datos compuestas** y su semántica de lectura/escritura dentro del modelo de entornos ya existente, manteniendo el patrón **visitor** sobre el árbol sintáctico generado por ANTLR4.

## Cambios respecto a la semana anterior

### Nuevas características en la gramática

- **Nueva regla de statement: asignación a arreglo (incluye acceso anidado)**
  ```antlr
  stmt
      : ...
      | ID ('[' index+=e ']')+ '=' assign=e  # ArrayAssignmentStatement
      ;
  ```
  - Habilita semánticamente la **mutación de arreglos** ya declarados, incluyendo rutas indexadas múltiples (por ejemplo, acceso tipo multidimensional).

- **Nuevas expresiones: literal de arreglo**
  ```antlr
  primary
      : ...
      | '[' e (',' e)* ']'               # ArrayExpression
      ;
  ```
  - Permite construir valores de tipo **array** en tiempo de ejecución a partir de una lista de expresiones evaluadas de izquierda a derecha.

- **Nuevas expresiones: acceso indexado a arreglo (anidado)**
  ```antlr
  primary
      : ...
      | ID ('[' e ']')+                  # ArrayAccessExpression
      ;
  ```
  - Agrega lectura de elementos por índice y encadenamiento de índices para navegar arreglos internos.

- **Nuevos tokens**
  - No se agregaron reglas léxicas nuevas; se reutilizan símbolos literales (`[` `]` `,` `=`) ya manejados por ANTLR como tokens implícitos.

- **Cambios en precedencia**
  - No hubo cambios en la jerarquía de precedencia (`eq -> ineq -> add -> prod -> unary -> primary`); las nuevas construcciones se integran en `primary` y en `stmt`.

- **Nuevos statements**
  - Se añade `ArrayAssignmentStatement` como nuevo tipo de sentencia con semántica de actualización in-place.

- **Nuevas expresiones**
  - Se añaden `ArrayExpression` y `ArrayAccessExpression`.

### Nuevas clases

En esta semana **no se introdujeron nuevas clases PHP** en `src/`.  
Se mantuvo el mismo conjunto de clases y se extendió su comportamiento.

### Clases modificadas

#### Environment.php

- **Qué se agregó**
  - Método `get_ref($key)` con retorno por referencia (`&`) para recuperar un símbolo del entorno sin copiar su valor.

- **Qué se cambió**
  - Se extiende la API del entorno para soportar acceso por referencia además de `get`, `set` y `assign`.

- **Por qué fue necesario**
  - La asignación sobre arreglos requiere modificar el valor almacenado en el entorno original (no una copia temporal).

- **Cómo afecta la ejecución del intérprete**
  - Hace posible que `a[1] = 10` actualice el arreglo real en el entorno activo o en la cadena de entornos padre.

- **Relación con clases existentes**
  - Interpreter.php depende de `get_ref` en `visitArrayAssignmentStatement` para implementar mutación indexada.

#### Interpreter.php

- **Qué se agregó**
  - Nuevos imports de contextos ANTLR:
    - `ArrayAssignmentStatementContext`
    - `ArrayExpressionContext`
    - `ArrayAccessExpressionContext`
  - Nuevos visitors:
    - `visitArrayAssignmentStatement`
    - `visitArrayExpression`
    - `visitArrayAccessExpression`

- **Qué se cambió**
  - Se amplía la evaluación de `stmt` y `primary` para incluir arreglos.
  - Se introduce validación de tipos de índice y verificación de rango durante acceso/asignación.

- **Por qué fue necesario**
  - Para soportar semánticamente el nuevo subconjunto del lenguaje orientado a estructuras indexadas.

- **Cómo afecta la ejecución del intérprete**
  - El intérprete ahora puede construir arreglos, leer posiciones específicas y modificar valores internos respetando el estado del entorno.

- **Relación con clases existentes**
  - Usa `Environment` (incluyendo `get_ref`) y mantiene compatibilidad con el sistema de control de flujo (`FlowType`) y llamadas (`Invocable`, `Foreign`).

### Cambios en el intérprete

- **Nuevos métodos visitor**
  - `visitArrayExpression`: evalúa cada elemento y retorna un `array` PHP.
  - `visitArrayAccessExpression`: resuelve el símbolo base y navega índices sucesivos.
  - `visitArrayAssignmentStatement`: obtiene referencia al arreglo base, navega niveles intermedios y asigna en el índice final.

- **Cambios en evaluación**
  - Se incorpora evaluación de expresiones compuestas por indexación múltiple.
  - La mutación de arreglos ocurre por referencia para preservar estado global/léxico.

- **Manejo de flujo**
  - No cambia el modelo de `FlowType` (`break`, `continue`, `return`); los nuevos visitors se integran sin alterar el control de flujo existente.

- **Nuevos tipos de retorno**
  - Se introduce retorno de valores de tipo **array** desde expresiones primarias.

- **Cambios en el entorno**
  - Se añade acceso por referencia (`get_ref`) para permitir escritura sobre estructuras almacenadas.

- **Nuevas validaciones semánticas**
  - Verificación de que la variable objetivo sea arreglo.
  - Verificación de índice entero en asignación.
  - Verificación de índice existente (rango) en lectura/escritura.
  - Verificación de que niveles intermedios sean arreglos en accesos anidados.

---

## Estructura del Proyecto

- **Grammar.g4**
  - Define la gramática del lenguaje (reglas de programa, sentencias, expresiones y precedencia).

- **`src/`**
  - Implementación del intérprete y runtime:
    - Interpreter.php, Environment.php, `FlowTypes.php`, `Invocable.php`, `Foreigns.php`, `Natives.php`.

- **bootstrap.php**
  - Carga manual de clases generadas por ANTLR y clases del intérprete.

- **index.php**
  - Punto de entrada web: recibe código, ejecuta lexer/parser, visita el árbol y muestra salida o errores.

- **`static/`**
  - Recursos de interfaz (CSS/JS) para el editor y consola en navegador.

- **`ANTLRv4/`**
  - Código PHP generado por ANTLR4 (`Lexer`, `Parser`, `Visitor`, clases base y metadatos).

- **`vendor/`**
  - Dependencias de Composer (runtime de ANTLR4 para PHP y autoload).

---

## Cómo generar el parser

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

- Este comando toma Grammar.g4 y genera lexer/parser para **PHP** dentro de `ANTLRv4/`.
- La opción **`-visitor`** es obligatoria porque el proyecto implementa la ejecución semántica mediante el patrón **Visitor** (`Interpreter extends GrammarBaseVisitor`).
- Conceptualmente se generan:
  - Analizador léxico (`GrammarLexer`)
  - Analizador sintáctico (`GrammarParser`)
  - Contrato visitor y clases base (`GrammarVisitor`, `GrammarBaseVisitor`)
  - Metadatos auxiliares (`.interp`, `.tokens`)

## Cómo ejecutar el proyecto

```bash
php -S 0.0.0.0:8080
```

- Inicia el servidor embebido de PHP en el puerto `8080`.
- El estudiante puede abrir el navegador, escribir código fuente del lenguaje en el editor y ejecutar para observar la salida.
- Flujo de interacción con el intérprete:
  - index.php recibe entrada
  - ANTLR genera tokens y árbol (`p`)
  - `Interpreter` visita el árbol y evalúa semánticamente
  - La consola muestra resultados o errores sintácticos/semánticos

## Conceptos aprendidos en esta semana

- **Estructuras de datos compuestas** en intérpretes (arreglos).
- **Acceso indexado** y navegación de estructuras anidadas.
- **Mutabilidad** y actualización in-place de estado.
- **Paso por referencia** en el runtime del intérprete.
- **Validación semántica** de índices y tipos.
- Integración de nuevas reglas ANTLR con el **visitor pattern**.
- Extensión incremental de gramática sin romper precedencia existente.
- Relación entre **entorno léxico** y almacenamiento de valores complejos.