<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Unit\Generator;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Generator\JsFileGenerator;

final class JsFileGeneratorTest extends TestCase
{
    private JsFileGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new JsFileGenerator();
    }

    public function testEmptyValuesReturnsEmptyString(): void
    {
        $this->assertSame('', $this->generator->generate([]));
    }

    public function testGeneratesModuleDefaultExport(): void
    {
        $code = $this->generator->generate(['API_URL' => 'https://api.example.com', 'DEBUG' => true]);

        $this->assertStringContainsString('export default {"API_URL":"https:\/\/api.example.com","DEBUG":true};', $code);
    }

    public function testGeneratesModuleNamedExport(): void
    {
        $code = $this->generator->generate(['foo' => 1], 'MyApp');

        $this->assertStringContainsString('export const MyApp = {"foo":1};', $code);
    }

    public function testGeneratesGlobals(): void
    {
        $code = $this->generator->generate(
            ['API_URL' => 'https://api.example.com', 'DEBUG' => true],
            null,
            JsFileGenerator::FORMAT_GLOBALS
        );

        $this->assertStringContainsString('var INJECTED_VALUES = {"API_URL":"https:\/\/api.example.com","DEBUG":true};', $code);
        $this->assertStringContainsString('var { API_URL, DEBUG } = INJECTED_VALUES;', $code);
    }

    public function testGeneratesGlobalsNamespace(): void
    {
        $code = $this->generator->generate(['foo' => 1], 'MyApp', JsFileGenerator::FORMAT_GLOBALS);

        $this->assertStringContainsString('var MyApp = {"foo":1};', $code);
    }

    public function testEscapesScriptClosingTag(): void
    {
        $code = $this->generator->generate(['FOO' => '</script>']);

        $this->assertStringNotContainsString('</script>', $code);
        $this->assertStringContainsString('<\/script>', $code);
    }

    public function testEscapesUnicode(): void
    {
        $code = $this->generator->generate(['MESSAGE' => 'café']);

        $this->assertStringNotContainsString("\u{00E9}", $code);
        $this->assertStringContainsString('\u00e9', $code);
    }

    public function testThrowsOnInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->generator->generate(['foo' => 1], null, 'invalid');
    }

    public function testThrowsOnInvalidNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->generator->generate(['foo' => 1], '123invalid');
    }

    public function testThrowsOnInvalidVariableName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->generator->generate(['123invalid' => 1]);
    }
}
