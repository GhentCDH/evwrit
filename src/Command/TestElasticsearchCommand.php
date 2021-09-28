<?php

namespace App\Command;

use App\Repository\TextRepository;
use App\Resource\ElasticCommunicativeGoalResource;
use App\Resource\ElasticTextResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestElasticsearchCommand extends Command
{
    protected static $defaultName = 'app:elasticsearch:test';
    protected static $defaultDescription = 'Test elasticsearch item representation.';

    protected $container = [];
    protected $di = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repository = $this->container->get('text_repository' );

        $service = $this->container->get('text_index_service');
        $service->setup();

        $text = $repository->indexQuery()->find(69108);
        $res = new ElasticTextResource($text);
        print_r($res->toJson(JSON_PRETTY_PRINT));
        return Command::SUCCESS;
    }
}
