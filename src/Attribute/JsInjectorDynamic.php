<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Attribute;

use Attribute;

/**
 * Marks a controller (class or action) as a page that receives the dynamic
 * injection values.
 *
 * Without a value, all dynamic providers (tag "rudak.injector.dynamic") are
 * collected. With a value, only the providers whose "channel" tag attribute
 * matches are collected, e.g.:
 *
 *     #[JsInjectorDynamic('user_context')]
 *     #[JsInjectorDynamic(['user_context', 'feature_flags'])]
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
final class JsInjectorDynamic
{
    /**
     * @param string|list<string>|null $providers One or more provider channels, or null for all
     */
    public function __construct(public readonly string|array|null $providers = null)
    {
    }
}
