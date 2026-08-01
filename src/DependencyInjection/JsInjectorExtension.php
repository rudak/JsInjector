<?php

declare(strict_types=1);

namespace Rudak\JsInjector\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class JsInjectorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('js_injector.output_path', $config['output_path']);
        $container->setParameter('js_injector.format', $config['format']);
        $container->setParameter('js_injector.namespace', $config['namespace']);
        $container->setParameter('js_injector.dynamic_script_tag_id', $config['dynamic_script_tag_id']);
        $container->setParameter('js_injector.dynamic_enabled', $config['dynamic_enabled']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
