<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Unit\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Command\RudakGenerateJsCommand;
use Rudak\JsInjector\DependencyInjection\JsInjectorExtension;
use Rudak\JsInjector\EventListener\DynamicValuesListener;
use Rudak\JsInjector\Harvester\DynamicValuesHarvester;
use Rudak\JsInjector\Harvester\ValuesHarvester;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class JsInjectorExtensionTest extends TestCase
{
    /**
     * @param array<string, mixed> $config
     */
    private function buildContainer(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.project_dir' => '/tmp',
            'kernel.environment' => 'test',
            'kernel.debug' => true,
        ]));

        $container->registerExtension(new JsInjectorExtension());
        $container->loadFromExtension('js_injector', $config);

        return $container;
    }

    public function testRegistersServices(): void
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.project_dir' => '/tmp']));
        (new JsInjectorExtension())->load([], $container);

        $this->assertTrue($container->hasDefinition(ValuesHarvester::class));
        $this->assertTrue($container->hasDefinition(DynamicValuesHarvester::class));
        $this->assertTrue($container->hasDefinition(DynamicValuesListener::class));
        $this->assertTrue($container->hasDefinition(RudakGenerateJsCommand::class));
    }

    public function testCompilesWithDefaultConfig(): void
    {
        $container = $this->buildContainer();
        $container->compile();

        $this->assertSame('public/bundles/rudakInjection/injection.js', $container->getParameter('js_injector.output_path'));
        $this->assertSame('module', $container->getParameter('js_injector.format'));
        $this->assertNull($container->getParameter('js_injector.namespace'));
        $this->assertSame('js-injector-dynamic', $container->getParameter('js_injector.dynamic_script_tag_id'));
        $this->assertFalse($container->getParameter('js_injector.dynamic_enabled'));
    }

    public function testCompilesWithCustomConfig(): void
    {
        $container = $this->buildContainer([
            'output_path' => 'public/app.js',
            'format' => 'globals',
            'namespace' => 'MyApp',
            'dynamic_script_tag_id' => 'app-dynamic',
            'dynamic_enabled' => true,
        ]);
        $container->compile();

        $this->assertSame('public/app.js', $container->getParameter('js_injector.output_path'));
        $this->assertSame('globals', $container->getParameter('js_injector.format'));
        $this->assertSame('MyApp', $container->getParameter('js_injector.namespace'));
        $this->assertSame('app-dynamic', $container->getParameter('js_injector.dynamic_script_tag_id'));
        $this->assertTrue($container->getParameter('js_injector.dynamic_enabled'));
    }
}
