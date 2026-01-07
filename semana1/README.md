# Semana 1

En esta semana aprenderemos qué es LIME, cómo configurar el entorno de desarrollo y realizaremos una demostración de LIME como parser.

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

* Para correr nuestro entorno dentro del contenedor de docker, navega a la carpeta del repositorio clonado de LIME y ejecuta el siguiente comando:

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

## Pasos para trabajar con LIME

1. Vamos a nuestra carpeta raiz de nuestro proyecto, ahí debemos clonar el repositorio de LIME.
2. Debemos descargar la imagen de docker php:5.6-cli.
3. Ejecutamos el contenedor de docker con el comando mencionado en los requisitos.
4. Instalamos las librerías necesarias dentro del contenedor de docker.
5. Navegamos a la carpeta de LIME dentro del contenedor de docker.
6. Eliminamos el archivo lime_scan_tokens y recompilamos:

```bash
cd lime
rm lime_scan_tokens
make lime_scan_tokens
```

Nos debe salir un output parecido a este:

```bash
root@e23444159469:/app/lime# make lime_scan_tokens
lex  -t lime_scan_tokens.l > lime_scan_tokens.c
cc    -c -o lime_scan_tokens.o lime_scan_tokens.c
cc   lime_scan_tokens.o   -o lime_scan_tokens
rm lime_scan_tokens.o lime_scan_tokens.c
root@e23444159469:/app/lime# 
```

7. Ahora podemos salir de la carpeta lime y trabajar donde harémos la gramática de nuestro parser en un archivo .lime, en este caso lo llamaremos ejemplo.lime.
8. Para generar el parser a partir de nuestro archivo .lime, ejecutamos el siguiente comando estando en el root:

```bash
php lime/lime.php ruta/ejemplo.lime > ruta/ejemplo.class
```

9. Ahora ya podemos usar el parser de la siguiente forma:

```php
 require 'lime/parse_engine.php';
 require 'ejemplo.class';
 //
 // Later:
 //
 $parser = new parse_engine(new my_parser());
 //
 // And still later:
 //
 try {
 	while (..something..) {
 		$parser->eat($type, $val);
 		// You figure out how to get the parameters.
 	}
 	// And after the last token has been eaten:
 	$parser->eat_eof();
 } catch (parse_error $e) {
 	die($e->getMessage());
 }
 return $parser->semantic;
```