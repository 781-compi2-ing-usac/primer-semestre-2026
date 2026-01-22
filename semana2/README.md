# Semana 2

En esta semana aprenderemos sobre las expresiones binarias utilizando un arbol de sintaxis abstracta (AST) y cómo implementar un parser con LIME con el patrón de diseño visitor para manejar estas expresiones.

## Requisitos

* Tener instalado docker en tu máquina. Puedes descargarlo desde https://www.docker.com/get-started
* Clonar el repositorio de LIME:

```bash
git clone https://github.com/rvanvelzen/lime.git
```
* Instalar la siguiente imagen de docker: php:5.6-cli

```bash
docker pull php:5.6-cli
```

* Para correr nuestro entorno dentro del contenedor de docker, navega a la carpeta raiz de tu proyecto y ejecuta el siguiente comando:

```bash
docker run -it --rm -v $(pwd):/app -w /app php:5.6-cli bash
```

* También para compilar el .class de nuestro parser, vamos a necesitar tener instaladas las siguientes librerías dentro del contenedor de docker:

```bash
printf "deb [trusted=yes] http://archive.debian.org/debian stretch main\n" > /etc/apt/sources.list \
&& printf "Acquire::Check-Valid-Until \"false\";\n" > /etc/apt/apt.conf.d/99no-check-valid \
&& apt-get update \
&& apt-get install -y flex gcc make \
&& rm -rf /var/lib/apt/lists/*
```

>[!NOTE]
> LIME es SOLO un generador de parser, por lo tanto dejé una ayuda para generar un scanner lexico en php con un pequeñlo script en python llamado `lexer_gen.py` aprenderemos como utilizarlo y como conectar el Lexer con el Parser generado por LIME.

# Semana 2 — Expresiones binarias y AST

## Cambios respecto a la semana anterior
- Introducción de una arquitectura basada en AST.
- Implementación del patrón Visitor.
- Creación de un lexer manual.
- Frontend básico para evaluación de expresiones.

## Descripción
El proyecto evoluciona hacia un intérprete funcional de expresiones aritméticas, separando claramente las fases de análisis y evaluación.
