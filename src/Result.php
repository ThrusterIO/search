<?php

declare(strict_types=1);

namespace Thruster\Search;

use ArrayAccess;

/**
 * Class Result
 *
 * @package Thruster\Search
 * @author  Aurimas Niekis <aurimas@niekis.lt>
 */
class Result implements ArrayAccess
{
    private array $rawResult;

    private ?string $index;

    private ?string $type;

    private ?string $id;

    private ?float $score;

    private array $source;

    public function __construct(array $rawResult)
    {
        $this->rawResult = $rawResult;

        $this->index  = $this->rawResult['_index'] ?? null;
        $this->type   = $this->rawResult['_type'] ?? null;
        $this->id     = $this->rawResult['_id'] ?? null;
        $this->score  = $this->rawResult['_score'] ?? null;
        $this->source = $this->rawResult['_source'] ?? [];
    }

    public function getRawResult(): array
    {
        return $this->rawResult;
    }

    public function getIndex(): ?string
    {
        return $this->index;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function getSource(): array
    {
        return $this->source;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->source[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->source[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->source[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->source[$offset]);
    }
}
