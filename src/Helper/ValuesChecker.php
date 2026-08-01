<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Helper;

use Rudak\JsInjector\Validator\VariableNameValidator;

final class ValuesChecker
{
    /**
     * Checks that every key of the given values is a valid JavaScript variable name.
     *
     * @param array<string, mixed> $values
     */
    public static function isValid(array $values): bool
    {
        return null === VariableNameValidator::findInvalidKey($values);
    }
}
