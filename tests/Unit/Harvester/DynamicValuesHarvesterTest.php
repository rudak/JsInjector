<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Unit\Harvester;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Harvester\DynamicValuesHarvester;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Harvester\ValuesHarvester;

final class DynamicValuesHarvesterTest extends TestCase
{
    public function testIsAValuesHarvester(): void
    {
        $harvester = new DynamicValuesHarvester([]);

        $this->assertInstanceOf(ValuesHarvester::class, $harvester);
    }

    public function testReturnsProviders(): void
    {
        $providers = [
            new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['FOO' => 1];
                }
            },
        ];

        $harvester = new DynamicValuesHarvester($providers);

        $this->assertSame($providers, [...$harvester->getValuesProviders()]);
    }
}
