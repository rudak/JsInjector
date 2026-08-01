<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Validator;

/**
 * Validates JavaScript variable names (identifiers and reserved keywords).
 *
 * Mirrors the client-side validation from js-src/utils/validation.js.
 */
final class VariableNameValidator
{
    /** JavaScript reserved keywords that cannot be used as variable names. */
    public const RESERVED_KEYWORDS = [
        'await',
        'break',
        'case',
        'catch',
        'class',
        'const',
        'continue',
        'debugger',
        'default',
        'delete',
        'do',
        'else',
        'enum',
        'export',
        'extends',
        'false',
        'finally',
        'for',
        'function',
        'if',
        'implements',
        'import',
        'in',
        'instanceof',
        'interface',
        'let',
        'new',
        'null',
        'package',
        'private',
        'protected',
        'public',
        'return',
        'static',
        'super',
        'switch',
        'this',
        'throw',
        'true',
        'try',
        'typeof',
        'var',
        'void',
        'while',
        'with',
        'yield',
    ];

    private const VALID_IDENTIFIER = '/^[a-zA-Z_$][a-zA-Z0-9_$]*$/';

    public static function isValid(string $name): bool
    {
        if ('' === $name) {
            return false;
        }

        if (in_array($name, self::RESERVED_KEYWORDS, true)) {
            return false;
        }

        return 1 === preg_match(self::VALID_IDENTIFIER, $name);
    }

    /**
     * Returns the first invalid key of the given values, or null when all are valid.
     *
     * @param array<string, mixed> $values
     */
    public static function findInvalidKey(array $values): ?string
    {
        foreach (array_keys($values) as $key) {
            if (!self::isValid((string) $key)) {
                return (string) $key;
            }
        }

        return null;
    }
}
