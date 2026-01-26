<?php

namespace App\Command;

use App\Repository\TextRepository;
use App\Resource\ElasticTextResource;
use App\Service\ElasticSearch\Index\TextIndexService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

class IndexTextElasticsearchCommand extends Command
{
    public function __construct(protected TextIndexService $textIndexService)
    {
        parent::__construct('app:elasticsearch:index-text');
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Index or update a single text in Elasticsearch.')
            ->addArgument('text_id', InputArgument::REQUIRED, 'The ID of the text to index')
            ->setHelp('This command allows you to index or update a single text in Elasticsearch by its ID.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $textId = $input->getArgument('text_id');

        $repository = new TextRepository();

        $service = $this->textIndexService;

        $text = $repository->defaultQuery()->find($textId);

        if (!$text) {
            $io->error("Text with ID {$textId} not found.");
            return Command::FAILURE;
        }

        $textResource = new ElasticTextResource($text);
        $service->add($textResource);

        $io->success("Successfully indexed text with ID {$textId}");

        return Command::SUCCESS;
    }
}
