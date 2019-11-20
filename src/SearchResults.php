<?php

declare(strict_types=1);

namespace Thruster\Search;

use ArrayAccess;
use ArrayIterator;
use Iterator;
use IteratorAggregate;
use JsonSerializable;

/**
 * Class SearchResults
 *
 * @package Thruster\Search
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class SearchResults implements ArrayAccess, IteratorAggregate, JsonSerializable
{
    private array $rawResult;

    private float $took;

    private int $total;

    private ?float $maxScore;

    /** @var Result[] */
    private array $results;

    private array $aggregations;

    public function __construct(array $rawResult)
    {
        $this->rawResult = $rawResult;

        $this->took         = $this->rawResult['took'] ?? -1;
        $this->total        = $this->rawResult['hits']['total']['value'] ?? $this->rawResult['hits']['total'] ?? -1;
        $this->maxScore     = $this->rawResult['hits']['max_score'] ?? null;
        $this->aggregations = [];
        $this->results      = [];

        foreach ($this->rawResult['hits']['hits'] as $hit) {
            $this->results[] = new Result($hit);
        }

        foreach ($this->rawResult['aggregations'] ?? [] as $name => $value) {
            if (isset($value['buckets'])) {
                $this->aggregations[$name] = [
                    'type'   => 'terms',
                    'result' => array_map(
                        static function ($value): array {
                            return [
                                'size'  => $value['doc_count'],
                                'value' => $value['key'],
                            ];
                        },
                        $value['buckets']
                    ),
                ];
            } else {
                $this->aggregations[$name] = [
                    'type'   => 'stats',
                    'result' => $value,
                ];
            }
        }
    }

    public function getRawResult(): array
    {
        return $this->rawResult;
    }

    public function getTook(): float
    {
        return $this->took;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getMaxScore(): ?float
    {
        return $this->maxScore;
    }

    /**
     * @return Result[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function getAggregations(): array
    {
        return $this->aggregations;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->results[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->results[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->results[$offset]);
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->results);
    }

    public function __toArray(): array
    {
        return [
            'took'      => $this->getTook(),
            'total'     => $this->getTotal(),
            'max_score' => $this->getMaxScore(),
            'results'   => iterator_to_array($this->getIterator()),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->__toArray();
    }
}
