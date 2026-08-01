<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Unit\Validator;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Validator\VariableNameValidator;

final class VariableNameValidatorTest extends TestCase
{
    public function testValidIdentifiers(): void
    {
        foreach (['foo', '_foo', '$foo', 'foo123', '_123', 'camelCase', 'UPPER_CASE'] as $name) {
            $this->assertTrue(VariableNameValidator::isValid($name), sprintf('%s should be valid', $name));
        }
    }

    public function testInvalidIdentifiers(): void
    {
        foreach (['', '123foo', 'foo-bar', 'foo bar', 'foo.bar'] as $name) {
            $this->assertFalse(VariableNameValidator::isValid($name), sprintf('%s should be invalid', $name));
        }
    }

    public function testReservedKeywords(): void
    {
        foreach (VariableNameValidator::RESERVED_KEYWORDS as $keyword) {
            $this->assertFalse(VariableNameValidator::isValid($keyword), sprintf('%s is a reserved keyword', $keyword));
        }
    }

    public function testFindInvalidKeyReturnsInvalidKey(): void
    {
        $this->assertSame('123bar', VariableNameValidator::findInvalidKey(['foo' => 1, '123bar' => 2]));
        $this->assertSame('class', VariableNameValidator::findInvalidKey(['class' => 1]));
    }

    public function testFindInvalidKeyReturnsNullWhenAllValid(): void
    {
        $this->assertNull(VariableNameValidator::findInvalidKey(['foo' => 1, '_bar' => 2]));
    }
}
