<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Harvester;

/**
 * Collects all services tagged with "rudak.injector".
 */
class ValuesHarvester
{
    /**
     * @param iterable<mixed> $valuesProviders
     */
    public function __construct(private readonly iterable $valuesProviders)
    {
    }

    /**
     * @return iterable<mixed>
     */
    public function getValuesProviders(): iterable
    {
        return $this->valuesProviders;
    }
}
