<?php

declare(strict_types=1);

namespace Rudak\JsInjector\Command;

use Rudak\JsInjector\Generator\JsFileGenerator;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Harvester\ValuesHarvester;
use Rudak\JsInjector\Helper\ValuesChecker;
use Rudak\JsInjector\Helper\VariableTypeHelper;
use Rudak\JsInjector\Validator\VariableNameValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'rudak:generate:js', description: 'Force JS file generation from PHP values')]
class RudakGenerateJsCommand extends Command
{
    public function __construct(
        private readonly ValuesHarvester $valuesHarvester,
        private readonly JsFileGenerator $jsFileGenerator,
        private readonly Filesystem $filesystem,
        private readonly string $projectDir,
        private readonly string $outputPath,
        private readonly string $format,
        private readonly ?string $namespace,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('JS FILE GENERATION');

        $valuesToInject = $this->collectValues($io);

        $this->showValuesToInject($io, $valuesToInject);
        $this->generateJsFile($io, $valuesToInject);

        $io->success('Job done !');

        return Command::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function collectValues(SymfonyStyle $io): array
    {
        $values = [];

        foreach ($this->valuesHarvester->getValuesProviders() as $provider) {
            if (!$provider instanceof HarvesterInterface) {
                $io->warning(sprintf('Service "%s" does not implement HarvesterInterface', get_debug_type($provider)));

                continue;
            }

            $providerValues = $provider->getValues();

            if (!ValuesChecker::isValid($providerValues)) {
                $invalidKey = VariableNameValidator::findInvalidKey($providerValues);
                $io->warning(sprintf('"%s" is not a correct variable name in %s', $invalidKey, get_class($provider)));

                continue;
            }

            $values = array_merge($providerValues, $values);
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function showValuesToInject(SymfonyStyle $io, array $values): void
    {
        $rows = array_map(
            static fn (string $key, mixed $value): array => [$key, VariableTypeHelper::getVariableType($value)],
            array_keys($values),
            array_values($values),
        );

        $io->table(['variable name', 'type'], $rows);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function generateJsFile(SymfonyStyle $io, array $values): void
    {
        $content = $this->jsFileGenerator->generate($values, $this->namespace, $this->format);
        $absolutePath = $this->projectDir.DIRECTORY_SEPARATOR.$this->outputPath;

        $this->filesystem->dumpFile($absolutePath, $content);
        $io->text(sprintf('Generated file : %s', $this->outputPath));
    }
}
