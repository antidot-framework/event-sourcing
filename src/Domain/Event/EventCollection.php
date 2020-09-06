<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Event;

use Antidot\EventSource\Domain\Model\Collection\Collection;

/**
 * @method static createImmutableCollection(array $items): self
 * @method static createMutableCollection(array $items = []): self
 * @method static createEmptyCollection(): self
 */
class EventCollection extends Collection
{
    protected function setFqcn(): void
    {
        $this->fqcn = AggregateChanged::class;
    }
}
