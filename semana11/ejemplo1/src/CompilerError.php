<?php

class CompilerError extends Exception {
    const PANIC_OOB_LABEL = "_panic_oob";
    const PANIC_OOM_LABEL = "_panic_oom";
    const PANIC_OOB_EXIT_CODE = 2;
    const PANIC_OOM_EXIT_CODE = 1;

    public static function semantic($message) {
        return new self($message);
    }
}
