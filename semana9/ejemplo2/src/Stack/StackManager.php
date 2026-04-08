<?php

namespace App\Stack;

use App\ARM\ASMGenerator;
use App\Env\Result;

class StackManager
{
    private $code;
    private $stackOffset = 0;
    private $tempRegister = "x9";

    public function __construct($codeGenerator) {
        $this->code = $codeGenerator;
    }

    public function pushValue($register) {
        $this->code->subi("sp", "sp", 16);
        $this->code->str($register, "sp");
        $this->stackOffset++;
    }

    public function popValue($destinationRegister = null) {
        if ($destinationRegister === null) {
            $destinationRegister = $this->tempRegister;
        }
        $this->code->ldr($destinationRegister, "sp");
        $this->code->addi("sp", "sp", 16);
        $this->stackOffset--;
        return $destinationRegister;
    }

    public function pushImmediate($immediate, $register = null) {
        if ($register === null) {
            $register = $this->tempRegister;
        }
        $this->code->li($register, $immediate);
        $this->pushValue($register);
    }

    public function popAndFree() {
        $this->popValue();
    }

    public function getStackOffset() {
        return $this->stackOffset * 16;
    }

    public function adjustStack($slots) {
        if ($slots > 0) {
            $this->code->addi("sp", "sp", $slots * 16);
            $this->stackOffset -= $slots;
        } elseif ($slots < 0) {
            $this->code->subi("sp", "sp", abs($slots) * 16);
            $this->stackOffset += abs($slots);
        }
    }
}
