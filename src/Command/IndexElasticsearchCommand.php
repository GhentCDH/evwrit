<?php

namespace App\Command;

use App\Repository\TextRepository;
use App\Resource\TextResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndexElasticsearchCommand extends Command
{
    protected static $defaultName = 'app:elasticsearch:index';
    protected static $defaultDescription = 'Drops the old elasticsearch index and recreates it.';

    protected $container = [];
    protected $di = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->setHelp('This command allows you to reindex elasticsearch.')
            ->addOption('index', 'i', InputOption::VALUE_OPTIONAL, 'Which index should be reindexed?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('index')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');


        $repository = $this->container->get('text_repository' );
        $service = $this->container->get('text_elastic_service');

        $repository->indexQuery()->where('text_id', '<', 100)->chunk(20,
            function($res) use ($service) {
                foreach ($res as $text) {
                    $res = new TextResource($text);
                    $service->add($res);
            }
        });

        return Command::SUCCESS;
    }
}
