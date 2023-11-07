<?php

namespace App\Command;

use App\Repository\TextRepository;
use App\Resource\ElasticTextLevelIndexResource;
use App\Resource\ElasticTextResource;
use App\Service\ElasticSearch\Index\LevelIndexService;
use App\Service\ElasticSearch\Index\TextIndexService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
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

        $allowedProjectIds = (array) $this->container->getParameter('app.allowed_project_ids');

        $count = 0;
        if ($index = $input->getArgument('index')) {
            switch ($index) {
                case 'text':
                    /** @var $repository TextRepository */
                    $repository = $this->container->get('text_repository' );

                    /** @var $service TextIndexService */
                    $service = $this->container->get('text_index_service');
                    $indexName = $service->createNewIndex();

                    $total = $repository->findByProjectIds($allowedProjectIds)->count();

                    $progressBar = new ProgressBar($output, $total);
                    $progressBar->start();

                    $repository->findByProjectIds($allowedProjectIds)->chunk(100,
                        function($res) use ($service, &$count, $progressBar) {
                            foreach ($res as $text) {
                                $res = new ElasticTextResource($text);
                                $service->add($res);
                                $count++;
                            }
                            $progressBar->advance(100);
                        });

                    $service->switchToNewIndex($indexName);

                    $progressBar->finish();

                    break;
                case "level": {
                    /** @var $repository TextRepository */
                    $repository = $this->container->get('text_repository' );

                    /** @var $service LevelIndexService */
                    $service = $this->container->get('level_index_service');
                    $indexName = $service->createNewIndex();

                    $total = $repository->findByProjectIds($allowedProjectIds)->count();

                    $progressBar = new ProgressBar($output, $total);
                    $progressBar->start();

                    $repository->findByProjectIds($allowedProjectIds)->chunk(100,
                        function($res) use ($service, &$count, $progressBar) {
                            foreach ($res as $text) {
                                foreach( $text->textLevels as $level ) {
                                    $res = new ElasticTextLevelIndexResource($level, $text);
                                    $service->add($res);
                                }
                                $count++;
                            }
                            $progressBar->advance(100);
                        });

                    $service->switchToNewIndex($indexName);

                    $progressBar->finish();
                }
            }
        }

        $io->success("Succesfully indexed {$count} records");

        return Command::SUCCESS;
    }
}
