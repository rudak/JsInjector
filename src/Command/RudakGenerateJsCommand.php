<?php

namespace Rudak\JsInjector\Command;

use App\Entity\User\Like;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Service\Bim;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Rudak\JsInjector\Harvester\ValuesHarvester;

class RudakGenerateJsCommand extends Command
{

    protected static $defaultName = 'rudak:generate:js';

    private          $valuesHarvester;

    /**
     * RudakGenerateJsCommand constructor.
     */
    public function __construct(ValuesHarvester $valuesHarvester)
    {
        $this->valuesHarvester = $valuesHarvester;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Force JS file generation from PHP values');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('JS FILE GENERATION');

        foreach ($this->valuesHarvester->getValuesProviders() as $provider) {
            if (!$provider instanceof HarvesterInterface) {
                return;
            }
            dump($provider->getValues());
        }
        $io->success('Js file generated');
    }
}
