<?php

declare(strict_types=1);

namespace Thruster\Search\Repository;

use Thruster\Search\SearchResults;

/**
 * Interface SearchRepositoryInterface
 *
 * @package Thruster\Search\Repository
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface SearchRepositoryInterface
{
    public function getIndexName(): string;

    public function getTypeName(): string;

    public function mapObject($object): array;

    public function mapping(): array;

    public function deleteIndex(): self;

    public function createIndex(): self;

    public function delete($object): void;

    public function reIndexObject($object): void;

    public function reIndex(int $batchSize = 100): void;

    public function search(array $query, int $size = 10, int $from = 0): SearchResults;

    public function find($id): ?array;
}
