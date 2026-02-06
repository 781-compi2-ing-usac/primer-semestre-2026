const editor = document.getElementById('editor');
const lineNumbers = document.getElementById('lineNumbers');

function updateLineNumbers() { 
    const lines = editor.value.split('\n').length;
    let numbers = '';
    for (let i = 1; i <= lines; i++) {
        numbers += i + '\n';
    } 
    lineNumbers.textContent = numbers; 
} 
// Inicializar
updateLineNumbers(); 
// Actualizar al escribir 
editor.addEventListener('input', updateLineNumbers);
editor.addEventListener('scroll', () => { lineNumbers.scrollTop = editor.scrollTop; });