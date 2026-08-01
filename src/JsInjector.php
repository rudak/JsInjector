<?php

declare(strict_types=1);

namespace Rudak\JsInjector;

use Rudak\JsInjector\DependencyInjection\JsInjectorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JsInjector extends Bundle
{
    public function getContainerExtension(): JsInjectorExtension
    {
        return new JsInjectorExtension();
    }
}
