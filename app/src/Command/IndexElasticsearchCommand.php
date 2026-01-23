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
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class IndexElasticsearchCommand extends Command
{
    protected array $di = [];

    public function __construct(protected TextIndexService $textIndexService, protected LevelIndexService $levelIndexService, protected ParameterBagInterface $params)
    {
        parent::__construct('app:elasticsearch:index');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Drops the old elasticsearch index and recreates it.')
            ->addArgument('index', InputArgument::REQUIRED, 'Which index should be reindexed?')
            ->addArgument('maxItems', InputArgument::OPTIONAL, 'Max number of items to index')
            ->addOption('chunk-size', null, InputArgument::OPTIONAL, 'Number of items to index per chunk', 50)
            ->setHelp('This command allows you to reindex elasticsearch.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $chunkSize = $input->getOption('chunk-size');

        $allowedProjectIds = (array) $this->params->get('app.allowed_project_ids');

        $count = 0;
        $maxItems = $input->getArgument('maxItems');
        if ($index = $input->getArgument('index')) {
            switch ($index) {
                case 'text':
                    $repository = new TextRepository();

                    $service = $this->textIndexService;
                    $indexName = $this->textIndexService->createNewIndex();

                    $total = $repository->findByProjectIds($allowedProjectIds)->count();

                    $progressBar = new ProgressBar($output, $total);
                    $progressBar->start();

                    $repository->findByProjectIds($allowedProjectIds)->chunk($chunkSize,
                        function($texts) use ($service, &$count, $progressBar, $maxItems, $chunkSize) {
                            if ( $maxItems && $count >= $maxItems ) {
                                return false;
                            }

                            // index traditions
                            $textResources = ElasticTextResource::collection($texts);
                            $count += $textResources->count();
                            $service->addMultiple($textResources);

                            // update progress bar
                            $progressBar->advance($texts->count());
                            return true;
                        }
                    );

                    $service->switchToNewIndex($indexName);

                    if (!$maxItems || $count < $maxItems ) {
                        $progressBar->setProgress($total);
                    }

                    break;
                case "level": {
                    $repository = new TextRepository();

                    $service = $this->levelIndexService;
                    $indexName = $service->createNewIndex();

                    $total = $repository->findByProjectIds($allowedProjectIds)->count();

                    $progressBar = new ProgressBar($output, $total);
                    $progressBar->start();

                    $repository->findByProjectIds($allowedProjectIds)->chunk($chunkSize,
                        function($texts) use ($service, &$count, $progressBar, $maxItems, $chunkSize) {
                            if ( $maxItems && $count >= $maxItems ) {
                                return false;
                            }

                            // index levels
                            $levelResources = [];
                            foreach ($texts as $text) {
                                foreach( $text->textLevels as $level ) {
                                    $levelResources[] = new ElasticTextLevelIndexResource($level, $text);
                                }
                                $count++;
                            }
                            $service->addMultiple($levelResources);

                            // update progress bar
                            $progressBar->advance($texts->count());
                            return true;
                        });

                    $service->switchToNewIndex($indexName);

                    if (!$maxItems || $count < $maxItems ) {
                        $progressBar->setProgress($total);
                    }
                }
            }
        }

        $io->success("Succesfully indexed {$count} records");

        return Command::SUCCESS;
    }
}
