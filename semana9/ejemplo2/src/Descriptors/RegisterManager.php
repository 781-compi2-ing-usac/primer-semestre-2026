<?php

namespace App\Descriptors;

use App\Env\Result;

class RegisterManager
{
    private $code;
    private $stackOffset = 0;

    public function __construct($code) {
        $this->code = $code;
    }

    public function push($value): Result {
        $this->code->subi("sp", "sp", 8);
        $this->code->li("x0", $value);
        $this->code->str("x0", "sp");
        $result = Result::stack(Result::INT, $this->stackOffset);
        $this->stackOffset += 8;
        return $result;
    }

    public function pop() {
        $this->code->ldr("x0", "sp");
        $this->code->addi("sp", "sp", 8);
        $this->stackOffset -= 8;
    }

    public function getStackOffset(): int {
        return $this->stackOffset;
    }
}
