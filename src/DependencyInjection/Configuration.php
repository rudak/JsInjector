<?php

declare(strict_types=1);

namespace Rudak\JsInjector\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('js_injector');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('output_path')
                    ->info('Path of the generated JS file, relative to the project directory.')
                    ->defaultValue('public/bundles/rudakInjection/injection.js')
                ->end()
                ->enumNode('format')
                    ->info('Output format: "module" (ES module, importable) or "globals" (classic script, global variables).')
                    ->values(['globals', 'module'])
                    ->defaultValue('module')
                ->end()
                ->scalarNode('namespace')
                    ->info('Optional namespace for the injected values (e.g. "MyApp").')
                    ->defaultNull()
                ->end()
                ->scalarNode('dynamic_script_tag_id')
                    ->info('HTML id of the <script type="application/json"> tag injected with the dynamic providers\' values.')
                    ->defaultValue('js-injector-dynamic')
                ->end()
                ->booleanNode('dynamic_enabled')
                    ->info('Enables dynamic injection on the pages marked with the #[JsInjectorDynamic] attribute.')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
