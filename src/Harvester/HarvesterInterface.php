<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Harvester;

interface HarvesterInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getValues(): array;
}
