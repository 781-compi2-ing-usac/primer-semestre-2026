<?php

class SymbolAccessEmitter {
    private $code;
    private $r;

    public function __construct($code, $registers) {
        $this->code = $code;
        $this->r = $registers;
    }

    public function loadScalarValueToT0($symbol, $baseReg) {
        if ($this->isByRef($symbol)) {
            $this->code->ldr($this->r["T2"], $baseReg, $symbol["offset"]);
            $this->code->ldr($this->r["T0"], $this->r["T2"], 0);
            return;
        }

        $this->code->ldr($this->r["T0"], $baseReg, $symbol["offset"]);
    }

    public function storeScalarFromT0($symbol, $baseReg) {
        if ($this->isByRef($symbol)) {
            $this->code->ldr($this->r["T2"], $baseReg, $symbol["offset"]);
            $this->code->str($this->r["T0"], $this->r["T2"], 0);
            return;
        }

        $this->code->str($this->r["T0"], $baseReg, $symbol["offset"]);
    }

    public function loadArrayPointerToT1($symbol, $baseReg) {
        if ($this->isByRef($symbol)) {
            $this->code->ldr($this->r["T2"], $baseReg, $symbol["offset"]);
            $this->code->ldr($this->r["T1"], $this->r["T2"], 0);
            return;
        }

        $this->code->ldr($this->r["T1"], $baseReg, $symbol["offset"]);
    }

    public function emitAddressOfSymbolToReg($symbol, $baseReg, $destReg) {
        if ($this->isByRef($symbol)) {
            $this->code->ldr($destReg, $baseReg, $symbol["offset"]);
            return;
        }

        $this->code->mov($destReg, $baseReg);
        if ($symbol["offset"] < 0) {
            $this->code->subi($destReg, $destReg, -$symbol["offset"]);
            return;
        }
        if ($symbol["offset"] > 0) {
            $this->code->addi($destReg, $destReg, $symbol["offset"]);
        }
    }

    private function isByRef($symbol) {
        return is_array($symbol)
            && array_key_exists("byRef", $symbol)
            && $symbol["byRef"] === true;
    }
}
