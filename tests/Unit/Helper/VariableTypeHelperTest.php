<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Helper\VariableTypeHelper;

final class VariableTypeHelperTest extends TestCase
{
    public function testSimpleTypes(): void
    {
        $this->assertSame('string', VariableTypeHelper::getVariableType('foo'));
        $this->assertSame('integer', VariableTypeHelper::getVariableType(42));
        $this->assertSame('double', VariableTypeHelper::getVariableType(1.5));
        $this->assertSame('boolean', VariableTypeHelper::getVariableType(true));
        $this->assertSame('NULL', VariableTypeHelper::getVariableType(null));
        $this->assertSame('object', VariableTypeHelper::getVariableType(new \stdClass()));
    }

    public function testFlatArray(): void
    {
        $this->assertSame('array [Elements: 3, Max depth: 1]', VariableTypeHelper::getVariableType([1, 2, 3]));
    }

    public function testNestedArray(): void
    {
        $this->assertSame(
            'array [Elements: 1, Max depth: 3]',
            VariableTypeHelper::getVariableType([[['deep']]])
        );
    }
}
