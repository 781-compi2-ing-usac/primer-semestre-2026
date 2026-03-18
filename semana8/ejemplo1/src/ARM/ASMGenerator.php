<?php

namespace App\ARM;

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

    private $instrucciones = [];
    private $r;
    private $labelCounter = 0;

    public function __construct() {        
        $this->r = include __DIR__ . "/Constants.php";
    }

    public function add($rd, $rs1, $rs2) {
        $this->instrucciones[] = new Instruction("add", $rd, $rs1, $rs2);
    }

    public function sub($rd, $rs1, $rs2) {
        $this->instrucciones[] = new Instruction("sub", $rd, $rs1, $rs2);
    }

    public function mul($rd, $rs1, $rs2) {
        $this->instrucciones[] = new Instruction("mul", $rd, $rs1, $rs2);
    }

    public function div($rd, $rs1, $rs2) {
        $this->instrucciones[] = new Instruction("sdiv", $rd, $rs1, $rs2);
    }

    public function and_op($rd, $rs1, $rs2) {
        $this->instrucciones[] = new Instruction("and", $rd, $rs1, $rs2);
    }

    public function orr($rd, $rs1, $rs2) {
        $this->instrucciones[] = new Instruction("orr", $rd, $rs1, $rs2);
    }

    public function mvn($rd, $rs) {
        $this->instrucciones[] = new Instruction("mvn", $rd, $rs);
    }

    public function cmp($rs1, $rs2) {
        $this->instrucciones[] = new Instruction("cmp", $rs1, $rs2);
    }

    public function cset($rd, $cond) {
        $this->instrucciones[] = new Instruction("cset", $rd, $cond);
    }

    public function addi($rd, $rs1, $imm) {
        $this->instrucciones[] = new Instruction("add", $rd, $rs1, "#".$imm);
    }

    public function subi($rd, $rs1, $imm) {
        $this->instrucciones[] = new Instruction("sub", $rd, $rs1, "#".$imm);
    }

    public function str($rs, $base, $offset=0) {
        $this->instrucciones[] = new Instruction("str", $rs, "[".$base.", #".$offset."]");
    }

    public function ldr($rd, $base, $offset=0) {
        $this->instrucciones[] = new Instruction("ldr", $rd, "[".$base.", #".$offset."]");
    }

    public function ldrl($rd, $label) {
        $this->instrucciones[] = new Instruction("ldr", $rd, "=".$label);
    }

    public function li($rd, $imm) {
        $this->instrucciones[] = new Instruction("mov", $rd, "#".$imm);
    }

    public function mov($rd, $rs) {
        $this->instrucciones[] = new Instruction("mov", $rd, $rs);
    }

    public function bl($label) {
        $this->instrucciones[] = new Instruction("bl", $label);
    }

    public function push($rd=null) {
        if ($rd === null) $rd = $this->r["T0"];
        $this->subi($this->r["SP"], $this->r["SP"], 16);
        $this->str($rd, $this->r["SP"]);
    }

    public function pop($rd=null) {
        if ($rd === null) $rd = $this->r["T0"];
        $this->ldr($rd, $this->r["SP"]);
        $this->addi($this->r["SP"], $this->r["SP"], 16);
    }

    public function syscall() {
        $this->instrucciones[] = new Instruction("svc", "#0");
    }

    public function printNewLine() {
        $this->comment("Salto de línea");
        $this->li($this->r["A0"], 1);
        $this->ldrl($this->r["A1"], "newline");
        $this->li($this->r["A2"], 1);
        $this->li($this->r["SYS"], 64);
        $this->syscall();
    }

    public function printInt($rd = null) {
        if ($rd === null) $rd = $this->r["A0"];

        $this->bl("itoa");
        $this->mov($this->r["A2"], $this->r["A1"]);
        $this->mov($this->r["A1"], $this->r["A0"]);
        $this->li($this->r["A0"], 1);
        $this->li($this->r["SYS"], 64);
        $this->syscall();
        $this->printNewLine();
    }

    public function endProgram() {
        $this->comment("Fin del programa");
        $this->li($this->r["A0"], 0);
        $this->li($this->r["SYS"], 93);
        $this->syscall();
    }

    public function comment($text) {
        $this->instrucciones[] = new Instruction("// ".$text);
    }

    public function itoa() {
        $itoa = "itoa:\n";
        $itoa .= "ldr x2, =buffer\n";
        $itoa .= "add x2, x2, #31\n";
        $itoa .= "mov w3, #0\n";
        $itoa .= "strb w3, [x2]\n";
        $itoa .= "mov x4, #10\n";
        $itoa .= "mov x5, x0\n";
        $itoa .= "loop:\n";
        $itoa .= "udiv x6, x5, x4\n";
        $itoa .= "msub x7, x6, x4, x5\n";
        $itoa .= "add x7, x7, #48\n";
        $itoa .= "sub x2, x2, #1\n";
        $itoa .= "strb w7, [x2]\n";
        $itoa .= "mov x5, x6\n";
        $itoa .= "cbnz x6, loop\n";
        $itoa .= "ldr x3, =buffer\n";
        $itoa .= "add x3, x3, #31\n";
        $itoa .= "sub x1, x3, x2\n";
        $itoa .= "mov x0, x2\n";
        $itoa .= "ret\n";
        return $itoa;
    }

    public function rodata(){
        $rodata = ".section .rodata\n";
        $rodata .= "newline: .asciz \"\\n\"\n";
        return $rodata;
    }

    public function toString() {
        $out = ".global _start\n";
        $out .= ".section .bss\n";
        $out .= "buffer: .skip 32\n";
        $out .= ".section .text\n";
        $out .= "_start:\n";

        foreach ($this->instrucciones as $inst) {
            $out .= "    ".$inst."\n";
        }
        $out .= $this->itoa();
        $out .= $this->rodata();
        return $out;
    }
}
