<?php

declare(strict_types=1);

namespace Thruster\Search\Repository;

/**
 * Interface SearchableRepositoryInterface
 *
 * @package Thruster\Search\Repository
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
interface SearchableRepositoryInterface
{
    public function searchableData(): iterable;
}
