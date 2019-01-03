<?php

namespace Rudak\JsInjector\Command;

use App\Entity\User\Like;
use Rudak\JsInjector\Harvester\HarvesterInterface;
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

    protected static $defaultName = 'rudak:generate:js';

    private          $valuesHarvester;

    /**
     * @var CacheManager
     */
    private $cacheManager;

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
        $io = new SymfonyStyle($input, $output);
        $io->title(' -- JS FILE GENERATION -- ');

        foreach ($this->valuesHarvester->getValuesProviders() as $provider) {
            if (!$provider instanceof HarvesterInterface) {
                return;
            }
            $this->valuesToInject = array_merge($provider->getValues(), $this->valuesToInject);
        }
        $this->cacheGeneration();

        $this->generateSymlink();
        $this->generateJsFile();

        $io->success('Js file generated');
    }

    private function cacheGeneration()
    {
        $cachePath = $this->cacheManager->getCachePath();
        $cache     = new ConfigCache($cachePath, true);

        if (!$cache->isFresh()) {
            $cache->write(json_encode($this->valuesToInject, JSON_PRETTY_PRINT));
            $this->generateSymlink();
        }
    }

    private function generateJsContent()
    {
        $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resources' . DIRECTORY_SEPARATOR . 'templates');
        $this->twig->setLoader($loader);

        return $this->twig->render('injection.js.twig', [
            'relativePath' => str_replace('/public', '', $this->getJsPath('injection.js')),
        ]);
    }

    private function generateSymlink()
    {
        $symlinkAbsolutePath = $this->project_dir . $this->getJsPath('data.json');
        $this->filesystem->symlink($this->cacheManager->getCachePath(), $symlinkAbsolutePath, true);
    }

    private function getJsPath($filename)
    {
        return '/public/bundles/rudakInjection/' . $filename;
    }

    private function generateJsFile()
    {
        $content                   = $this->generateJsContent();
        $injectionFileAbsolutePath = $this->project_dir . $this->getJsPath('injection.js');
        $this->filesystem->dumpFile($injectionFileAbsolutePath, $content);
    }
}
