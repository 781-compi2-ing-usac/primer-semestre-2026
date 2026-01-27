# Semana 1

En esta semana aprenderemos qué es ANTLR, cómo configurar el entorno de desarrollo y realizaremos una demostración de ANTLR como parser.

## Requisitos:

- Usaremos linux en el transcurso del curso.
- Instalaremos las siguientes herramientas:

```bash
sudo apt install antlr4 php composer
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