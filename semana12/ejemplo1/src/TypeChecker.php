<?php

class TypeChecker {
    public static function typeToString($type) {
        if (is_string($type)) {
            return $type;
        }

        if (!is_array($type) || !array_key_exists("kind", $type)) {
            return "unknown";
        }

        if ($type["kind"] !== "array") {
            return $type["kind"];
        }

        $elem = self::typeToString($type["elem"]);
        $rank = array_key_exists("rank", $type) ? $type["rank"] : 1;
        $dims = array_key_exists("dims", $type) ? $type["dims"] : [];
        $dimText = empty($dims) ? "" : " dims=" . implode("x", $dims);
        return "array<" . $elem . "> rank=" . $rank . $dimText;
    }

    public static function typeEquals($a, $b) {
        if (is_array($a)
            && array_key_exists("kind", $a)
            && $a["kind"] === "array"
            && is_array($b)
            && array_key_exists("kind", $b)
            && $b["kind"] === "array") {
            return self::arrayTypeEqualsFlexible($a, $b);
        }

        if (is_string($a) && is_string($b)) {
            return $a === $b;
        }

        if (!is_array($a) || !is_array($b)) {
            return false;
        }

        if (!array_key_exists("kind", $a) || !array_key_exists("kind", $b)) {
            return false;
        }

        if ($a["kind"] !== $b["kind"]) {
            return false;
        }

        if ($a["kind"] !== "array") {
            return true;
        }

        if (!array_key_exists("elem", $a) || !array_key_exists("elem", $b)) {
            return false;
        }

        $rankA = array_key_exists("rank", $a) ? $a["rank"] : 1;
        $rankB = array_key_exists("rank", $b) ? $b["rank"] : 1;
        if ($rankA !== $rankB) {
            return false;
        }

        $dimsA = array_key_exists("dims", $a) ? $a["dims"] : null;
        $dimsB = array_key_exists("dims", $b) ? $b["dims"] : null;
        if ($dimsA !== null && $dimsB !== null && $dimsA !== $dimsB) {
            return false;
        }

        return self::typeEquals($a["elem"], $b["elem"]);
    }

    private static function arrayTypeEqualsFlexible($a, $b) {
        if (!array_key_exists("elem", $a) || !array_key_exists("elem", $b)) {
            return false;
        }

        if (!self::typeEquals($a["elem"], $b["elem"])) {
            return false;
        }

        $rankA = array_key_exists("rank", $a) ? $a["rank"] : 1;
        $rankB = array_key_exists("rank", $b) ? $b["rank"] : 1;
        if ($rankA !== $rankB) {
            return false;
        }

        $dimsA = array_key_exists("dims", $a) ? $a["dims"] : null;
        $dimsB = array_key_exists("dims", $b) ? $b["dims"] : null;
        if ($dimsA === null || $dimsB === null) {
            return true;
        }

        return $dimsA === $dimsB;
    }

    public static function isIntType($type) {
        return is_string($type) && $type === "int";
    }

    public static function isBoolType($type) {
        return is_string($type) && $type === "bool";
    }

    public static function isIntOrBoolType($type) {
        return self::isIntType($type) || self::isBoolType($type);
    }

    public static function isArrayType($type) {
        return is_array($type)
            && array_key_exists("kind", $type)
            && $type["kind"] === "array"
            && array_key_exists("elem", $type);
    }

    public static function makeArrayTypeWithRankAndDims($elemType, $rank, $dims) {
        return [
            "kind" => "array",
            "elem" => $elemType,
            "rank" => $rank,
            "dims" => $dims
        ];
    }
}
