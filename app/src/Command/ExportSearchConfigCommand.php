<?php

namespace App\Command;

use App\Repository\TextRepository;
use App\Resource\ElasticCommunicativeGoalResource;
use App\Resource\ElasticTextResource;
use App\Service\ElasticSearch\Search\Configs;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExportSearchConfigCommand extends Command
{
    protected static $defaultName = 'app:exportconfig:search';
    protected static $defaultDescription = 'Export search filter id\'s.';

    protected ContainerInterface $container;
    protected array $di = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keys = [];
        $keys['metadata'] = array_keys(Configs::filterPhysicalInfo());
        $keys['administrative'] = array_keys(Configs::filterAdministrative());
        $keys['communicative'] = array_keys(Configs::filterCommunicativeInfo());
        $keys['materiality'] = array_keys(Configs::filterMateriality());
        $keys['attestation'] = array_keys(Configs::filterAttestations()['attestations']['filters']);
        $keys['base_annotations'] = array_keys(Configs::filterBaseAnnotations()['annotations']['filters']);
        $keys['text_structure'] = array_keys(Configs::filterTextStructure()['annotations']['filters']);

        print_r($keys);
        return 0;
    }
}
