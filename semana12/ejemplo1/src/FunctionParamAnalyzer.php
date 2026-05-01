<?php

class FunctionParamAnalyzer {
    public function analyze($functionCtx, $params) {
        $argTypes = [];
        $argModes = [];
        $blockText = $functionCtx->block()->getText();

        foreach ($params as $paramName) {
            if ($this->isArrayParameter($blockText, $paramName)) {
                $argTypes[] = [
                    "kind" => "array",
                    "elem" => "int",
                    "rank" => 1
                ];
                $argModes[] = "by_ref";
                continue;
            }

            $argTypes[] = "int";
            $argModes[] = "by_value";
        }

        return [
            "argTypes" => $argTypes,
            "argModes" => $argModes
        ];
    }

    private function isArrayParameter($blockText, $paramName) {
        $name = preg_quote($paramName, '/');

        if (preg_match('/(?<![A-Za-z0-9_])' . $name . '\[/', $blockText)) {
            return true;
        }

        if (preg_match('/\blen\(' . $name . '\)/', $blockText)) {
            return true;
        }

        if (preg_match('/(?<![A-Za-z0-9_])' . $name . '=\[/', $blockText)) {
            return true;
        }

        return false;
    }
}
