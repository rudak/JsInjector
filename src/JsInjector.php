<?php

namespace Rudak\JsInjector;

use Rudak\JsInjector\DependencyInjection\JsInjectorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class JsInjector
 *
 * @package Rudak\JsInjector
 */
class JsInjector extends Bundle
{
    public function getContainerExtension()
    {
        return new JsInjectorExtension();
    }
}