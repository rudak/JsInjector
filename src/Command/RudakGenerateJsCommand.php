<?php

namespace Rudak\JsInjector\Command;

use App\Entity\User\Like;
use Rudak\JsInjector\Harvester\HarvesterInterface;
use Rudak\JsInjector\Helper\ValuesChecker;
use Rudak\JsInjector\Helper\ValuesNormalizer;
use Rudak\JsInjector\Helper\VariableTypeHelper;
use Rudak\JsInjector\Service\Bim;
use Rudak\JsInjector\Service\CacheManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Rudak\JsInjector\Harvester\ValuesHarvester;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Filesystem\Filesystem;

class RudakGenerateJsCommand extends Command
{
    const JS_FILENAME = 'injection.js';

    protected static $defaultName = 'rudak:generate:js';

    /**
     * @var ValuesHarvester
     */
    private $valuesHarvester;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var array
     */
    private $valuesToInject;

    /**
     * @var EngineInterface
     */
    private $twig;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $project_dir;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * RudakGenerateJsCommand constructor.
     * @param ValuesHarvester   $valuesHarvester
     * @param CacheManager      $cacheManager
     * @param \Twig_Environment $twig
     * @param Filesystem        $filesystem
     * @param string            $project_dir
     */
    public function __construct(ValuesHarvester $valuesHarvester, CacheManager $cacheManager, \Twig_Environment $twig, Filesystem $filesystem, string $project_dir)
    {
        $this->valuesHarvester = $valuesHarvester;
        $this->cacheManager    = $cacheManager;
        $this->valuesToInject  = [];
        $this->twig            = $twig;
        $this->filesystem      = $filesystem;
        $this->project_dir     = $project_dir;
        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Force JS file generation from PHP values');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $this->io->title(' -- JS FILE GENERATION -- ');

        foreach ($this->valuesHarvester->getValuesProviders() as $provider) {
            if (!$provider instanceof HarvesterInterface) {
                return;
            }
            if (true !== $checkResult = ValuesChecker::isValid($provider->getValues())) {
                $this->io->warning(sprintf('"%d" is not a correct variable name in %s', $checkResult, get_class($provider)));
                continue;
            }
            $this->valuesToInject = array_merge($provider->getValues(), $this->valuesToInject);
        }

        $this->showValuesToInject();
        $this->generateJsFile();

        $this->io->success('Job done !');
    }


    private function getJsPath($filename)
    {
        return '/public/bundles/rudakInjection/' . $filename;
    }

    private function generateJsFile()
    {
        $injectionFileAbsolutePath = $this->project_dir . $this->getJsPath(self::JS_FILENAME);
        $this->filesystem->dumpFile($injectionFileAbsolutePath, $this->getJsFileContent());
        $this->io->text(sprintf('Generated file : %s', $this->getJsPath(self::JS_FILENAME)));
    }

    private function getJsFileContent()
    {
        $templateDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'templates';
        $loader            = new \Twig_Loader_Filesystem($templateDirectory);
        $this->twig->setLoader($loader);

        return $this->twig->render('injection.js.twig', $this->getJsTemplateData());
    }

    private function getJsTemplateData()
    {
        return count($this->valuesToInject)
            ? [
                'jsonContent'   => $this->getJsonValuesForJsFile(),
                'variableNames' => $this->getVariablesNames(),
            ]
            : [
                'jsonContent'   => null,
                'variableNames' => null,
            ];
    }

    private function getJsonValuesForJsFile()
    {
        return json_encode($this->valuesToInject);
    }

    private function getVariablesNames()
    {
        return array_keys($this->valuesToInject);
    }

    private function showValuesToInject()
    {
        $valuesTypes = array_map(function ($key, $value) {
            return [
                $key, VariableTypeHelper::getVariableType($value),
            ];
        }, array_keys($this->valuesToInject), array_values($this->valuesToInject));
        $this->io->table(['variable name', 'type'], $valuesTypes);
    }
}
