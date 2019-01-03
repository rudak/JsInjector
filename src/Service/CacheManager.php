<?php

namespace Rudak\JsInjector\Service;

class CacheManager
{
    /**
     * @var string
     */
    private $cacheDir;


    /**
     * CacheManager constructor.
     * @param string $cacheDir
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    public function getCachePath()
    {
        $cachePath = $this->cacheDir . DIRECTORY_SEPARATOR . 'rudakJsInjector';
        if (!file_exists($cachePath)) {
            mkdir($cachePath);
        }

        $cachePath = $cachePath . DIRECTORY_SEPARATOR . 'data.json';

        return $cachePath;
    }
}