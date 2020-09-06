<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Model\ValueObject;

abstract class AggregateRootId
{
    protected string $aggregateIdd;

    final protected function __construct(string $id)
    {
        $this->aggregateIdd = $id;
    }

    public static function fromString(string $id): self
    {
        return new static($id);
    }

    public function value(): string
    {
        return $this->aggregateIdd;
    }
}
