<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Command;

use Rudak\JsInjector\Harvester\DynamicValuesHarvester;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Harvester\ValuesHarvester;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Debug command: lists the injector providers (channels) and the data they provide.
 */
#[AsCommand(name: 'rudak:debug:injection', description: 'List the injector providers (channels) and the data they provide')]
class RudakDebugInjectionCommand extends Command
{
    private const MAX_VALUE_LENGTH = 60;

    public function __construct(
        private readonly ValuesHarvester $valuesHarvester,
        private readonly DynamicValuesHarvester $dynamicValuesHarvester,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('INJECTOR PROVIDERS');

        $this->showDynamicChannels($io);
        $this->showStaticProviders($io);

        $io->success('Done');

        return Command::SUCCESS;
    }

    private function showDynamicChannels(SymfonyStyle $io): void
    {
        $rows = [];

        foreach ($this->dynamicValuesHarvester->getValuesProviders() as $channel => $provider) {
            if (!$provider instanceof HarvesterInterface) {
                $rows[] = [(string) $channel, get_debug_type($provider), '(does not implement HarvesterInterface)'];

                continue;
            }

            $rows[] = [(string) $channel, get_class($provider), $this->formatValues($provider->getValues())];
        }

        $io->section('Dynamic channels (tag: rudak.injector.dynamic)');
        $io->table(['channel', 'provider', 'data'], $rows);
    }

    private function showStaticProviders(SymfonyStyle $io): void
    {
        $rows = [];

        foreach ($this->valuesHarvester->getValuesProviders() as $provider) {
            if (!$provider instanceof HarvesterInterface) {
                $rows[] = [get_debug_type($provider), '(does not implement HarvesterInterface)'];

                continue;
            }

            $rows[] = [get_class($provider), $this->formatValues($provider->getValues())];
        }

        $io->section('Static providers (tag: rudak.injector, written to the generated file)');
        $io->table(['provider', 'data'], $rows);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function formatValues(array $values): string
    {
        $parts = [];

        foreach ($values as $key => $value) {
            $parts[] = sprintf('%s: %s', $key, $this->shorten($this->stringify($value)));
        }

        return implode(', ', $parts);
    }

    private function stringify(mixed $value): string
    {
        if (null === $value) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            try {
                return (string) json_encode($value, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                return sprintf('array(%d)', count($value));
            }
        }

        if (is_object($value)) {
            return get_debug_type($value);
        }

        return (string) $value;
    }

    private function shorten(string $value): string
    {
        if (strlen($value) <= self::MAX_VALUE_LENGTH) {
            return $value;
        }

        return substr($value, 0, self::MAX_VALUE_LENGTH - 3).'...';
    }
}
