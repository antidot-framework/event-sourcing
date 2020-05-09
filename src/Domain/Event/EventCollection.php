<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Event;

use Antidot\EventSource\Domain\Model\Collection\Collection;

class EventCollection extends Collection
{
    protected function setFqcn(): void
    {
        $this->fqcn = AggregateChanged::class;
    }
}
