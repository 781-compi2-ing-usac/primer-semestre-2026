<?php

return [
    "time" => [
        "kind" => "native_fn",
        "arity" => 0,
        "argTypes" => [],
        "returnType" => "int",
        "label" => "_native_time",
        "emitter" => "emitNativeTime"
    ],
    "len" => [
        "kind" => "native_fn",
        "arity" => 1,
        "argTypes" => [[
            "kind" => "array",
            "elem" => "int",
            "rank" => 1
        ]],
        "argModes" => ["by_value"],
        "returnType" => "int",
        "label" => "_native_len",
        "emitter" => "emitNativeLen"
    ]
];
