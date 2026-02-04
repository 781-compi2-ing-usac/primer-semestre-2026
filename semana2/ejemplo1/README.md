# Semana 2

## Comandos para generar parser:

``` bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

En esta semana aprenderemos sobre las expresiones binarias utilizando un arbol de sintaxis abstracta (AST) y cómo implementar un parser con LIME con el patrón de diseño visitor para manejar estas expresiones.

# Semana 2 — Expresiones binarias y AST

## Cambios respecto a la semana anterior
- Introducción de una arquitectura basada en AST.
- Implementación del patrón Visitor.
- Frontend básico para evaluación de expresiones.

## Descripción
El proyecto evoluciona hacia un intérprete funcional de expresiones aritméticas, separando claramente las fases de análisis y evaluación.
