<?php

class Instruction {

    public $instr;
    public $op1;
    public $op2;
    public $op3;

    public function __construct($instr, $op1=null, $op2=null, $op3=null) {
        $this->instr = $instr;
        $this->op1 = $op1;
        $this->op2 = $op2;
        $this->op3 = $op3;
    }

    public function __toString() {

        $ops = [];

        if ($this->op1 !== null) $ops[] = $this->op1;
        if ($this->op2 !== null) $ops[] = $this->op2;
        if ($this->op3 !== null) $ops[] = $this->op3;

        if (count($ops) === 0) return $this->instr;

        return $this->instr . " " . implode(", ", $ops);
    }
}

class ASMGenerator {

    private $instr = [];
    private $r;

    public function __construct() {        
        $this->r = include __DIR__ . "/Constants.php";
    }

    public function add($rd, $rs1, $rs2) {
        $this->instr[] = new Instruction("add", $rd, $rs1, $rs2);
    }

    public function sub($rd, $rs1, $rs2) {
        $this->instr[] = new Instruction("sub", $rd, $rs1, $rs2);
    }

    public function mul($rd, $rs1, $rs2) {
        $this->instr[] = new Instruction("mul", $rd, $rs1, $rs2);
    }

    public function div($rd, $rs1, $rs2) {
        $this->instr[] = new Instruction("sdiv", $rd, $rs1, $rs2);
    }

    public function addi($rd, $rs1, $imm) {
        $this->instr[] = new Instruction("add", $rd, $rs1, "#".$imm);
    }

    public function subi($rd, $rs1, $imm) {
        $this->instr[] = new Instruction("sub", $rd, $rs1, "#".$imm);
    }

    public function str($rs, $base, $offset=0) {
        $this->instr[] = new Instruction(
            "str",
            $rs,
            "[".$base.", #".$offset."]"
        );
    }

    public function ldr($rd, $base, $offset=0) {
        $this->instr[] = new Instruction(
            "ldr",
            $rd,
            "[".$base.", #".$offset."]"
        );
    }

    public function ldrl($rd, $label) {
        $this->instr[] = new Instruction(
            "ldr",
            $rd,
            "=".$label
        );
    }


    public function li($rd, $imm) {
        $this->instr[] = new Instruction("mov", $rd, "#".$imm);
    }

    public function mov($rd, $rs) {
        $this->instr[] = new Instruction("mov", $rd, $rs);
    }

    public function cmp($rs1, $rs2) {
        $this->instr[] = new Instruction("cmp", $rs1, $rs2);
    }

    public function cset($rd, $cond) {
        $this->instr[] = new Instruction("cset", $rd, $cond);
    }

    public function label($name) {
        $this->instr[] = new Instruction($name . ":");
    }

    public function b($label) {
        $this->instr[] = new Instruction("b", $label);
    }

    public function bcond($cond, $label) {
        $this->instr[] = new Instruction("b." . $cond, $label);
    }

    public function cbz($rs, $label) {
        $this->instr[] = new Instruction("cbz", $rs, $label);
    }

    public function bl($label) {
        $this->instr[] = new Instruction("bl", $label);
    }

    public function push($rd=null) {

        if ($rd === null) $rd = $this->r["T0"];

        $this->subi($this->r["SP"], $this->r["SP"], 8);
        $this->str($rd, $this->r["SP"]);
    }

    public function pop($rd=null) {

        if ($rd === null) $rd = $this->r["T0"];

        $this->ldr($rd, $this->r["SP"]);
        $this->addi($this->r["SP"], $this->r["SP"], 8);
    }

    public function syscall() {
        $this->instr[] = new Instruction("svc", "#0");
    }

    public function printNewLine() {
        $this->comment("Preparando salto de línea");
        $this->li($this->r["A0"], 1); // stdout
        $this->ldrl($this->r["A1"], "newline");
        $this->li($this->r["A2"], 1);
        $this->li($this->r["SYS"], 64);  // write syscall
        $this->syscall();
    }

    public function printInt($rd = null) {
        if ($rd === null) $rd = $this->r["A0"];

        $this->bl("itoa");
        $this->comment("Retorno: A0 con puntero al buffer, A1 con longitud");
        $this->comment("Preparando argumentos para syscall write");
        $this->mov($this->r["A2"], $this->r["A1"]);  
        $this->mov($this->r["A1"], $this->r["A0"]);
        $this->li($this->r["A0"], 1); // stdout        
        $this->li($this->r["SYS"], 64);  // write syscall
        $this->syscall();        
        $this->printNewLine();
    }

    public function endProgram() {
        $this->comment("Terminando el programa");
        $this->li($this->r["A0"], 0); // exit code 0
        $this->li($this->r["SYS"], 93);  // exit syscall
        $this->syscall();
    }

    public function comment($text) {
        $this->instr[] = new Instruction("// ".$text);
    }

    public function itoa() {
        $code = "itoa:\n";                    
        $code .= "// x0 = integer\n";
        $code .= "// returns:\n";
        $code .= "// x0 = buffer ptr\n";
        $code .= "// x1 = length\n";
        $code .= "ldr x2, =buffer\n";
        $code .= "add x2, x2, #31\n";
        $code .= "mov w3, #0\n";
        $code .= "strb w3, [x2]\n";

        $code .= "mov x5, x0\n";        
        $code .= "mov x4, #10\n";        
        $code .= "// Sign Flag \n";
        $code .= "mov x10, #0\n";

        $code .= "// Check if negative \n";
        $code .= "cmp x5, #0\n";
        $code .= "bge loop\n";
        $code .= "neg x5, x5\n";        
        $code .= "mov x10, #1\n";

        $code .= "loop:\n";
        $code .= "udiv x6, x5, x4\n";
        $code .= "msub x7, x6, x4, x5\n";
        $code .= "add x7, x7, #48\n";
        $code .= "sub x2, x2, #1\n";
        $code .= "strb w7, [x2]\n";

        $code .= "mov x5, x6\n";
        $code .= "cbnz x6, loop\n";

        $code .= "cmp x10, #0\n";
        $code .= "beq done\n";
        $code .= "sub x2, x2, #1\n";
        $code .= "mov w7, #45\n";
        $code .= "strb w7, [x2]\n";

        $code .= "done:\n";
        $code .= "ldr x3, =buffer\n";
        $code .= "add x3, x3, #31\n";
        $code .= "sub x1, x3, x2\n";

        $code .= "mov x0, x2\n";
        $code .= "ret\n";
        return $code;
    }

    public function rodata(){
        $code = ".section .rodata\n";
        $code .= "newline: .asciz \"\\n\"\n";
        return $code;
    }

    public function toString() {
        $out = ".global _start\n";
        $out .= ".section .bss\n";
        // Buffer para imprimir enteros de hasta 10 dígitos + signo + null terminator
        $out .= "buffer: .skip 32\n"; 
        $out .= "heap_base: .skip 1048576\n";
        $out .= "heap_end:\n";
        $out .= ".section .text\n";        
        $out .= "_start:\n";

        foreach ($this->instr as $inst) {
            $str = (string)$inst;
            $out .= $str . "\n";
        }        
        $out .= $this->itoa();
        $out .= $this->rodata();
        $out .= "\n";
        return $out;
    }
}
