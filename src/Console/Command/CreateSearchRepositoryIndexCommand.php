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
 * Class CreateSearchRepositoryIndexCommand
 *
 * @package Thruster\Search\Console\Command
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class CreateSearchRepositoryIndexCommand extends Command
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
            ->setName('repositories:search:create')
            ->setDescription('Create Search Indexes')
            ->addArgument('repositoryNames', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Repository Names')
            ->addOption('delete', 'd', InputOption::VALUE_NONE, 'Deletes old repositories before creating new');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repositoryNames = $input->getArgument('repositoryNames');
        if (count($repositoryNames) < 1) {
            $repositoryNames = array_keys($this->repositories->all());
        }

        if (count($repositoryNames) < 1) {
            $io->warning('Nothing to do.');

            return 0;
        }

        $deleteRepositoryBefore = $input->getOption('delete');
        if ($deleteRepositoryBefore && false === $io->confirm(
                'Do you really want to delete repositories before creating new?',
                false
            )
        ) {
            $io->comment('That\'s okay, nothing will be done...');

            return 0;
        }

        $progressBar = $io->createProgressBar(count($repositoryNames));
        /** @var SearchRepositoryInterface $repository */
        foreach ($repositoryNames as $repositoryName) {
            $progressBar->setMessage('Creating repository "' . $repositoryName . '"');

            if ($deleteRepositoryBefore) {
                $this->repositories->getRepository($repositoryNames)->deleteIndex();
            }

            $this->repositories->get($repositoryNames)->createIndex();

            $progressBar->advance();
        }

        $io->success('All repositories were created successfully!');

        return 0;
    }
}
