<?php
namespace Rudak\JsInjector\Harvester;

class ValuesHarvester
{
    private $valuesProviders;

    public function __construct(iterable $valuesProviders)
    {
        $this->valuesProviders = $valuesProviders;
    }

    /**
     * @return iterable
     */
    public function getValuesProviders(): iterable
    {
        return $this->valuesProviders;
    }


}