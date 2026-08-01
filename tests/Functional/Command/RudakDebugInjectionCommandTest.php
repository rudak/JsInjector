<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Tests\Functional\Command;

use PHPUnit\Framework\TestCase;
use Rudak\JsInjector\Command\RudakDebugInjectionCommand;
use Rudak\JsInjector\Harvester\DynamicValuesHarvester;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Harvester\ValuesHarvester;
use Symfony\Component\Console\Tester\CommandTester;

final class RudakDebugInjectionCommandTest extends TestCase
{
    public function testListsDynamicChannelsAndTheirData(): void
    {
        $dynamic = new DynamicValuesHarvester([
            'user_context' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['USER_ID' => 42, 'ROLE' => 'admin'];
                }
            },
            'feature_flags' => new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['CHAT_ENABLED' => true];
                }
            },
        ]);

        $static = new ValuesHarvester([
            new class implements HarvesterInterface {
                public function getValues(): array
                {
                    return ['API_URL' => 'https://api.example.com'];
                }
            },
        ]);

        $command = new RudakDebugInjectionCommand($static, $dynamic);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([]);

        $this->assertSame(0, $exitCode);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('user_context', $output);
        $this->assertStringContainsString('USER_ID: 42', $output);
        $this->assertStringContainsString('feature_flags', $output);
        $this->assertStringContainsString('CHAT_ENABLED: true', $output);
        $this->assertStringContainsString('API_URL: https://api.example.com', $output);
    }
}
