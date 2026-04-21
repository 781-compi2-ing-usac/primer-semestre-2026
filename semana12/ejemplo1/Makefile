# Template: ARM64 en host x86 y x64 con QEMU (flujo multi-fuente)
# Copia este archivo como Makefile dentro de la lección.

# ----------------------------------------------------------
# Toolchain (compilación cruzada AArch64)
# ----------------------------------------------------------
AS      = aarch64-linux-gnu-as
LD      = aarch64-linux-gnu-ld
QEMU    = qemu-aarch64

# ----------------------------------------------------------
# Rutas y nombres de salida
# ----------------------------------------------------------
BUILD   = build
TARGET  = $(BUILD)/main

# ----------------------------------------------------------
# Fuentes
# ----------------------------------------------------------
MAIN_SRC = main.s
AUX_SRCS = $(filter-out $(MAIN_SRC), $(wildcard *.s))
SRCS     = $(MAIN_SRC) $(AUX_SRCS)
OBJS     = $(patsubst %.s,$(BUILD)/%.o,$(SRCS))

# ----------------------------------------------------------
# Flags
# ----------------------------------------------------------
ASFLAGS = -g
LDFLAGS =

# ----------------------------------------------------------
# Targets principales
# ----------------------------------------------------------

all: $(TARGET)

# Crear el directorio build si no existe
$(BUILD):
	mkdir -p $(BUILD)

# Ensamblado de cualquier archivo .s a .o
$(BUILD)/%.o: %.s | $(BUILD)
	$(AS) $(ASFLAGS) -o $@ $<

# Enlazado final
$(TARGET): $(OBJS)
	$(LD) $(LDFLAGS) -o $@ $(OBJS)

# ----------------------------------------------------------
# Ejecución (QEMU)
# ----------------------------------------------------------
run: $(TARGET)
	$(QEMU) $(TARGET)

# ----------------------------------------------------------
# Depuración (QEMU + GDB remoto)
# ----------------------------------------------------------
gdb: $(TARGET)
	@echo "Starting QEMU and waiting for GDB on port 1234..."
	$(QEMU) -g 1234 $(TARGET)

# ----------------------------------------------------------
# Limpieza
# ----------------------------------------------------------
clean:
	rm -rf $(BUILD)

# ----------------------------------------------------------
# Información de ayuda
# ----------------------------------------------------------
info:
	@echo "Targets disponibles:"
	@echo "  make        -> ensambla y enlaza el programa ARM64"
	@echo "  make run    -> ejecuta el binario en QEMU"
	@echo "  make gdb    -> ejecuta QEMU y espera conexión de GDB (VS Code)"
	@echo "  make clean  -> elimina el directorio build/"
