# Semana 8 - Compilador con Expresiones Booleanas

## Descripción

Compilador que traduce código fuente a ensamblador ARM64 (AArch64) usando QEMU.
Sigue la estructura del ejemplo2 de semana 8 con **traits** para separar responsabilidades.

## Estructura del Proyecto

```
semana9/ejemplo2/
├── Grammar.g4              # Gramática ambigua del lenguaje
├── ANTLRv4/                # Parser generado por ANTLR
├── vendor/                 # Dependencias (ANTLR runtime)
├── src/
│   ├── Compiler.php        # Visitor principal
│   ├── ARM/
│   │   ├── ASMGenerator.php  # Emisor de código ARM64
│   │   └── Constants.php     # Mapeo de registros
│   └── Ast/
│       ├── Expresiones/
│       │   ├── Aritmeticas.php  # +, -, *, /
│       │   ├── Booleanas.php    # &&, ||, !, ==, !=, <, >, <=, >=
│       │   └── Primitivos.php   # INT, FLOAT, ID
│       └── Sentencias/
│           └── PrintF.php        # print()
├── index.php               # Interfaz web
├── compile.php            # API de compilación
├── run.php                # API de compilación + ejecución
├── build.sh               # Script para compilar con QEMU
└── main.asm               # Ejemplo de salida
```

## Operadores Soportados

| Operador | Descripción | Instrucciones ARM64 |
|----------|-------------|-------------------|
| `+` | Suma | `add` |
| `-` | Resta | `sub` |
| `*` | Multiplicación | `mul` |
| `/` | División | `sdiv` |
| `&&` | AND lógico | `and` |
| `\|\|` | OR lógico | `orr` |
| `!` | NOT | `mvn` |
| `==`, `!=` | Comparación | `cmp` + `cset` |
| `<`, `<=`, `>`, `>=` | Relacionales | `cmp` + `cset` |

## Cómo generar el parser

```bash
antlr4 -Dlanguage=PHP Grammar.g4 -visitor -o ANTLRv4/
```

## Cómo ejecutar

```bash
php -S 0.0.0.0:8080 -t .
```

Luego abre: `http://localhost:8080`

## Compilar y ejecutar código ARM64

```bash
chmod +x build.sh
./build.sh
```

Requiere:
```bash
sudo apt install qemu-system libvirt-daemon-system virt-manager
sudo apt install binutils-aarch64-linux-gnu qemu-user build-essential
sudo usermod -aG libvirt $USER
```

## Ejemplos de prueba

```go
print(5 > 3);           // Imprime 1
print(5 == 5);          // Imprime 1
print(true && false);   // Imprime 0
print(!false);          // Imprime 1
print(3 + 2 * 4 > 10);  // Imprime 1
print((5 > 3) || (2 < 1)); // Imprime 1
```
