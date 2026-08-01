<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Helper;

final class VariableTypeHelper
{
    public static function getVariableType(mixed $value): string
    {
        $type = gettype($value);

        if ('array' === $type) {
            return sprintf('%s [Elements: %d, Max depth: %d]', $type, count($value), self::arrayDepth($value));
        }

        return $type;
    }

    /**
     * @param array<mixed> $array
     */
    private static function arrayDepth(array $array): int
    {
        $maxDepth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = 1 + self::arrayDepth($value);

                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }

        return $maxDepth;
    }
}
