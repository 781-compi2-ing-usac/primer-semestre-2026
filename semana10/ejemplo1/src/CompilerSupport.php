<?php

use Context\ArrayExpressionContext;
use Context\ArrayAccessExpressionContext;
use Context\ArrayAssignmentStatementContext;

class CompilerSupport {
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

    public static function normalizeExprList($list) {
        if ($list === null) {
            return [];
        }

        if (is_array($list)) {
            return $list;
        }

        if ($list instanceof Traversable) {
            return iterator_to_array($list, false);
        }

        return [$list];
    }

    public static function getArrayLiteralElements(ArrayExpressionContext $ctx) {
        if (!method_exists($ctx, "e")) {
            return [];
        }
        return self::normalizeExprList($ctx->e());
    }

    public static function getArrayAccessIndexes(ArrayAccessExpressionContext $ctx) {
        if (property_exists($ctx, "e")) {
            return self::normalizeExprList($ctx->e);
        }

        if (method_exists($ctx, "e")) {
            return self::normalizeExprList($ctx->e());
        }

        return [];
    }

    public static function getArrayAssignParts(ArrayAssignmentStatementContext $ctx) {
        $indexes = [];
        $assignExpr = null;

        if (property_exists($ctx, "index")) {
            $indexes = self::normalizeExprList($ctx->index);
        }

        if (property_exists($ctx, "assign")) {
            $assignExpr = $ctx->assign;
        }

        if (empty($indexes) || $assignExpr === null) {
            $allExprs = [];
            if (method_exists($ctx, "e")) {
                $allExprs = self::normalizeExprList($ctx->e());
            }
            if (count($allExprs) > 0) {
                $assignExpr = $allExprs[count($allExprs) - 1];
                $indexes = array_slice($allExprs, 0, count($allExprs) - 1);
            }
        }

        return ["indexes" => $indexes, "assign" => $assignExpr];
    }

    private static function getArrayLiteralContext($node) {
        if ($node instanceof ArrayExpressionContext) {
            return $node;
        }

        if (!is_object($node) || !method_exists($node, "getChildCount") || !method_exists($node, "getChild")) {
            return null;
        }

        $count = $node->getChildCount();
        for ($i = 0; $i < $count; $i++) {
            $child = $node->getChild($i);
            $found = self::getArrayLiteralContext($child);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    private static function concatLists($lists) {
        $out = [];
        foreach ($lists as $list) {
            foreach ($list as $item) {
                $out[] = $item;
            }
        }
        return $out;
    }

    public static function analyzeArrayLiteralNode($expr, $inferTypeFn) {
        $arrayCtx = self::getArrayLiteralContext($expr);

        if ($arrayCtx === null) {
            $type = $inferTypeFn($expr);
            if (!self::isIntOrBoolType($type)) {
                throw new Exception("En esta etapa, los arrays solo admiten escalares int o bool; se obtuvo " . self::typeToString($type));
            }
            return [
                "scalarType" => $type,
                "dims" => [],
                "exprs" => [$expr]
            ];
        }

        $elements = self::getArrayLiteralElements($arrayCtx);
        if (count($elements) === 0) {
            throw new Exception("No se permiten arrays vacíos en esta etapa");
        }

        $allArrays = true;
        $allScalars = true;
        foreach ($elements as $el) {
            $isArray = self::getArrayLiteralContext($el) !== null;
            if ($isArray) {
                $allScalars = false;
            } else {
                $allArrays = false;
            }
        }

        if (!$allArrays && !$allScalars) {
            throw new Exception("Array multidimensional debe ser rectangular y no mezclar escalares con sub-arrays");
        }

        if ($allScalars) {
            $scalarType = null;
            foreach ($elements as $el) {
                $currentType = $inferTypeFn($el);
                if ($scalarType === null) {
                    $scalarType = $currentType;
                    continue;
                }

                if (!self::typeEquals($scalarType, $currentType)) {
                    throw new Exception("Array literal requiere elementos homogéneos, se obtuvo " . self::typeToString($scalarType) . " y " . self::typeToString($currentType));
                }
            }

            if (!self::isIntOrBoolType($scalarType)) {
                throw new Exception("En esta etapa, los arrays solo admiten escalares int o bool; se obtuvo " . self::typeToString($scalarType));
            }

            return [
                "scalarType" => $scalarType,
                "dims" => [count($elements)],
                "exprs" => $elements
            ];
        }

        $childInfos = [];
        foreach ($elements as $el) {
            $childInfos[] = self::analyzeArrayLiteralNode($el, $inferTypeFn);
        }

        $firstType = $childInfos[0]["scalarType"];
        $firstDims = $childInfos[0]["dims"];
        foreach ($childInfos as $info) {
            if (!self::typeEquals($firstType, $info["scalarType"])) {
                throw new Exception("Sub-arrays deben tener el mismo tipo escalar base");
            }
            if ($firstDims !== $info["dims"]) {
                throw new Exception("Array multidimensional debe ser rectangular (sub-arrays con mismas dimensiones)");
            }
        }

        return [
            "scalarType" => $firstType,
            "dims" => array_merge([count($elements)], $firstDims),
            "exprs" => self::concatLists(array_map(function($x) { return $x["exprs"]; }, $childInfos))
        ];
    }
}
