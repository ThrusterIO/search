<?php

declare(strict_types=1);

namespace Thruster\Search;

use Thruster\Search\Repository\SearchRepositoryInterface;

/**
 * Class Repositories
 *
 * @package Thruster\Search
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Repositories
{
    /** @var SearchRepositoryInterface[] */
    private array $repositories;

    public function __construct(iterable $repositories)
    {
        $this->repositories = [];

        $this->setRepositories($repositories);
    }

    public function addRepository(SearchRepositoryInterface $repository): self
    {
        $this->repositories[$repository->getIndexName()] = $repository;

        return $this;
    }

    public function hasRepository(string $name): bool
    {
        return isset($this->repositories[$name]);
    }

    public function getRepository(string $name): SearchRepositoryInterface
    {
        if (false === $this->hasRepository($name)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Search repository "%s" does not exists',
                    $name
                )
            );
        }

        return $this->repositories[$name];
    }

    public function setRepositories(iterable $repositories): self
    {
        foreach ($repositories as $repository) {
            $this->addRepository($repository);
        }

        return $this;
    }

    /**
     * @return SearchRepositoryInterface[]
     */
    public function all(): array
    {
        return $this->repositories;
    }
}
