<?php

namespace App\Command;

use App\Repository\TextRepository;
use App\Resource\ElasticTextLevelResource;
use App\Resource\ElasticTextResource;
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

    protected $projects = [
        'ERC (main corpus)', 'Post-doc Bentein', 'Serena', 'Emmanuel'
    ];

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

                    $total = $repository->findByProjectNames($this->projects)->count();

                    $progressBar = new ProgressBar($output, $total);
                    $progressBar->start();

                    $repository->findByProjectNames($this->projects)->chunk(100,
                        function($res) use ($service, &$count, $progressBar) {
                            foreach ($res as $text) {
                                $res = new ElasticTextResource($text);
                                $service->add($res);
                                $count++;
                            }
                            $progressBar->advance(100);
                        });

                    $progressBar->finish();

                    break;
                case "level": {
                    /** @var $repository TextRepository */
                    $repository = $this->container->get('text_repository' );

                    /** @var $service TextIndexService */
                    $service = $this->container->get('level_index_service');
                    $service->setup();

                    $total = $repository->findByProjectNames($this->projects)->count();

                    $progressBar = new ProgressBar($output, $total);
                    $progressBar->start();

                    $repository->findByProjectNames($this->projects)->chunk(100,
                        function($res) use ($service, &$count, $progressBar) {
                            foreach ($res as $text) {
                                foreach( $text->textLevels as $level ) {
                                    $res = new ElasticTextLevelResource($level);
                                    $service->add($res);
                                }
                                $count++;
                            }
                            $progressBar->advance(100);
                        });

                    $progressBar->finish();

//                    $text_id = 3768;
//                    $text_ids = [
//                        55571, 48907,
//                        13305,
//                        60762,
//                        54666,
//                        31663,
//                        6230,
//                        66083,
//                        31136,
//                        55979,
//                        65622,
//                        53135,
//                        10490,
//                        9199,
//                        10449,
//                        13306,
//                        10639,
//                        5169,
//                        8701,
//                        10369,
//                        58372,
//                        3285,
//                        9299,
//                        250
//                    ];
//
//                    /** @var $repository TextRepository */
//                    $repository = $this->container->get('text_repository' );
//
//                    /** @var $service TextIndexService */
//                    $service = $this->container->get('level_index_service');
//                    $service->setup();
//
//                    foreach( $text_ids as $text_id ) {
//                        $text = $repository->find($text_id);
//                        foreach( $text->textLevels as $level ) {
//                            $res = new ElasticTextLevelResource($level);
//                            $service->add($res);
//                        }
//                    }
                }
            }
        }

        $io->success("Succesfully indexed {$count} records");

        return Command::SUCCESS;
    }
}
