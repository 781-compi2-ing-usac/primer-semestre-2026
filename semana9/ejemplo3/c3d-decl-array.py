# precondiciones
heap = [None]*5
stack = [None]*5
STACKPOINTER = 0
HEAPPOINTER = 0
FRAMEPOINTER = 0  # referencia absoluta del codigo ejecutado actualmente


# declaracion array
x = [1, 2, 3]
y = "asdfg"

# en c3d

t1 = STACKPOINTER
t2 = HEAPPOINTER # 0

heap[t2 + 0 ] = 3  # almacenamos longitud
HEAPPOINTER = HEAPPOINTER + 1 
    #mov x1, 0 cargamos nuestro valor relativo hacia un registro
    #add x0, x1 sumamos hacia nuestro pointer del valor en el registro
    # str 3, [x0] movemos a memoria nuestro valor
    # add x1, 1 aumentamos nuestro indice relativo
heap[t2 + 1 ] = 1
HEAPPOINTER = HEAPPOINTER + 1
heap[t2 + 2 ] = 2
HEAPPOINTER = HEAPPOINTER + 1
heap[t2 + 3 ] = 3 str
HEAPPOINTER = HEAPPOINTER + 1 #HeapPointer = 3
stack[t1] = t2 push


def imprimir ():
    #proceso de impresion en assembler -->
    #recuperar un valor en memoria/registro 
    #convertilo a su caracter correspondiente
    #Se lanza al stdout
    #repetir
    for element in range(heap[stack[t1]]):
        print (heap[element+1])



print( STACKPOINTER, HEAPPOINTER)
print ( heap )
print ( stack )

imprimir()