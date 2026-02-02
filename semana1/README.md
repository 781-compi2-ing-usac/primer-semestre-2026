# Semana 1

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