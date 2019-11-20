<?php

declare(strict_types=1);

namespace Thruster\Search\Repository;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Thruster\Search\SearchResults;

/**
 * Class BaseSearchRepository
 *
 * @package Thruster\Search\Repository
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
abstract class BaseSearchRepository implements SearchRepositoryInterface
{
    protected SearchableRepositoryInterface $databaseRepository;

    protected Client $client;

    /**
     * @param  Client  $client
     *
     * @return BaseSearchRepository

     * @required
     */
    public function setClient(Client $client): BaseSearchRepository
    {
        $this->client = $client;

        return $this;
    }

    public function getDatabaseRepository(): SearchableRepositoryInterface
    {
        return $this->databaseRepository;
    }

    protected function getIndexSettings(): array
    {
        return [
            'number_of_shards'   => 3,
            'number_of_replicas' => 2,
        ];
    }

    protected function getAliases(): ?array
    {
        return [];
    }

    protected function getObjectId(object $object)
    {
        if (method_exists($object, 'getId')) {
            return $object->getId();
        }

        if (property_exists($object, 'id')) {
            return $object->id;
        }
    }

    public function reIndex(int $batchSize = 100): void
    {
        $rows = $this->getDatabaseRepository()->searchableData();

        $body = [];
        $i    = 1;
        foreach ($rows as $row) {
            $body[] = [
                'index' => [
                    '_index' => $this->getIndexName(),
                    '_type'  => $this->getTypeName(),
                    '_id'    => $this->getObjectId($row[0]),
                ],
            ];

            $body[] = $this->mapObject($row[0]);

            if ($i % $batchSize === 0) {
                $this->client->bulk(['body' => $body]);
                $body = [];
            }

            $i++;
        }
        if (count($body) > 0) {
            $this->client->bulk(['body' => $body]);
        }
    }

    public function reIndexObject($object): void
    {
        $this->client->index(
            [
                'index' => $this->getIndexName(),
                'type'  => $this->getTypeName(),
                'id'    => $this->getObjectId($object),
                'body'  => $this->mapObject($object),
            ]
        );
    }

    public function find($id): ?array
    {
        $result = $this->client->get(
            [
                'index' => $this->getIndexName(),
                'type'  => $this->getTypeName(),
                'id'    => $id,
            ]
        );
        if ($result['found'] !== 1) {
            return null;
        }

        return $result['_source'];
    }

    public function search(array $query, int $size = 10, int $from = 0): SearchResults
    {
        $result = $this->client->search(
            [
                'index' => $this->getIndexName(),
                'type'  => $this->getTypeName(),
                'body'  => $query,
                'from'  => $from,
                'size'  => $size,
            ]
        );

        return new SearchResults($result);
    }

    public function delete($object): void
    {
        $id = $this->getObjectId($object);

        $this->client->delete(
            [
                'index' => $this->getIndexName(),
                'type'  => $this->getTypeName(),
                'id'    => $id,
            ]
        );
    }

    public function createIndex(): self
    {
        $this->client->indices()->create(
            $this->getIndexCreationSettings()
        );

        return $this;
    }

    public function deleteIndex(): self
    {
        try {
            $this->client->indices()->delete(
                ['index' => $this->getIndexName()]
            );
        } catch (Missing404Exception $e) {
        }

        return $this;
    }

    protected function getIndexCreationSettings(): array
    {
        $body    = [
            'settings' => $this->getIndexSettings(),
            'mappings' => [
                $this->getTypeName() => $this->mapping(),
            ],
        ];

        $aliases = $this->getAliases();
        if (count($aliases) > 0) {
            $body['aliases'] = $this->getAliases();
        }

        return [
            'index' => $this->getIndexName(),
            'body'  => $body,
        ];
    }
}
