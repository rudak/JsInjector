<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Harvester;

/**
 * Collects all services tagged with "rudak.injector.dynamic".
 *
 * These providers are resolved on every request (kernel.response listener),
 * so their values are always fresh at each page load, unlike the static
 * providers (tag "rudak.injector") consumed by the "rudak:generate:js" command.
 */
final class DynamicValuesHarvester extends ValuesHarvester
{
}
