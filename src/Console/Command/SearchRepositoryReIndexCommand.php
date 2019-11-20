<?php

declare(strict_types=1);

namespace Thruster\Search\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Thruster\Search\Repositories;
use Thruster\Search\Repository\SearchRepositoryInterface;

/**
 * Class SearchRepositoryReIndexCommand
 *
 * @package Thruster\Search\Console\Command
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SearchRepositoryReIndexCommand extends Command
{
    private Repositories $repositories;

    public function __construct(Repositories $repositories)
    {
        parent::__construct();

        $this->repositories = $repositories;
    }

    protected function configure(): void
    {
        $this
            ->setName('repositories:search:reindex')
            ->setDescription('Reindex search repository using data from searchable repository')
            ->addArgument('repositoryName', InputArgument::REQUIRED, 'Repository Name')
            ->addOption('batchSize', 's', InputOption::VALUE_REQUIRED, 'Batch Size', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repositoryName = $input->getArgument('repositoryName');
        $repository = $this->repositories->getRepository($repositoryName);

        $io->comment('ReIndexing: "' . $repositoryName . '"');
        $repository->reIndex((int) $input->getOption('batchSize'));
        $io->success('Finished Successfully!');

        return 0;
    }
}
