<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Functional\Command;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Command\RudakGenerateJsCommand;
use Rudak\JsInjector\Generator\JsFileGenerator;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Harvester\ValuesHarvester;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class RudakGenerateJsCommandTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir().'/jsinjector_test_'.uniqid();
        (new Filesystem())->mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->tempDir);
    }

    private function buildCommand(ValuesHarvester $harvester, ?string $namespace, string $format): RudakGenerateJsCommand
    {
        return new RudakGenerateJsCommand(
            $harvester,
            new JsFileGenerator(),
            new Filesystem(),
            $this->tempDir,
            'public/bundles/rudakInjection/injection.js',
            $format,
            $namespace,
        );
    }

    private function readGeneratedFile(): string
    {
        $file = $this->tempDir.'/public/bundles/rudakInjection/injection.js';
        $this->assertFileExists($file);

        return file_get_contents($file);
    }

    public function testGeneratesModuleByDefault(): void
    {
        $harvester = new ValuesHarvester([
            new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['API_URL' => 'https://api.example.com', 'DEBUG' => true];
                }
            },
        ]);

        $command = $this->buildCommand($harvester, null, JsFileGenerator::FORMAT_MODULE);
        $tester = new CommandTester($command);

        $this->assertSame(0, $tester->execute([]));

        $content = $this->readGeneratedFile();
        $this->assertStringContainsString('export default {"API_URL":"https:\/\/api.example.com","DEBUG":true};', $content);
    }

    public function testGeneratesGlobalsFormat(): void
    {
        $harvester = new ValuesHarvester([
            new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['API_URL' => 'https://api.example.com', 'DEBUG' => true];
                }
            },
        ]);

        $command = $this->buildCommand($harvester, null, JsFileGenerator::FORMAT_GLOBALS);
        $tester = new CommandTester($command);

        $tester->execute([]);

        $content = $this->readGeneratedFile();
        $this->assertStringContainsString('var INJECTED_VALUES =', $content);
        $this->assertStringContainsString('var { API_URL, DEBUG } = INJECTED_VALUES;', $content);
    }

    public function testModuleFormatUsesNamespaceAsNamedExport(): void
    {
        $harvester = new ValuesHarvester([
            new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['timeout' => 5000];
                }
            },
        ]);

        $command = $this->buildCommand($harvester, 'MyApp', JsFileGenerator::FORMAT_MODULE);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $content = $this->readGeneratedFile();
        $this->assertStringContainsString('export const MyApp = {"timeout":5000};', $content);
    }

    public function testSkipsProviderWithInvalidKeys(): void
    {
        $harvester = new ValuesHarvester([
            new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['123invalid' => 1];
                }
            },
            new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['VALID' => 2];
                }
            },
        ]);

        $command = $this->buildCommand($harvester, null, JsFileGenerator::FORMAT_MODULE);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('is not a correct variable name', $output);

        $content = $this->readGeneratedFile();
        $this->assertStringContainsString('VALID', $content);
        $this->assertStringNotContainsString('123invalid', $content);
    }
}
