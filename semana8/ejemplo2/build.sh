#!/bin/bash

mkdir -p build

aarch64-linux-gnu-as -g -o build/main.o main.asm
aarch64-linux-gnu-ld -o build/main build/main.o

echo "Ejecutando con QEMU:"
qemu-aarch64 build/main
