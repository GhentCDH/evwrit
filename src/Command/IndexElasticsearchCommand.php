<?php

namespace App\Command;

use App\Repository\TextRepository;

use App\Resource\ElasticTextResource;
use App\Service\ElasticSearch\TextIndexService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('index', InputArgument::REQUIRED, 'Which index should be reindexed?')
            ->setHelp('This command allows you to reindex elasticsearch.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = 0;
        if ($index = $input->getArgument('index')) {
            switch ($index) {
                case 'text':
                    /** @var $repository TextRepository */
                    $repository = $this->container->get('text_repository' );

                    /** @var $service TextIndexService */
                    $service = $this->container->get('text_index_service');
                    $service->setup();

                    $repository->findByProjectNames(['ERC (main corpus)', 'Post-doc Bentein', 'Serena', 'Emmanuel'])->chunk(100,
                        function($res) use ($service, &$count) {
                            foreach ($res as $text) {
                                $res = new ElasticTextResource($text);
                                $service->add($res);
                                $count++;
                            }
                        });

                    break;
            }
        }

        $io->success("Succesfully indexed {$count} records");

        return Command::SUCCESS;
    }
}
