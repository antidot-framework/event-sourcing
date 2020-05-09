<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Model\Aggregate;

use Antidot\EventSource\Domain\Event\AggregateChanged;
use Antidot\EventSource\Domain\Event\EventCollection;

abstract class AggregateRoot
{
    public const EVENT_MAP = [];
    protected array $events;

    public function __construct()
    {
        $this->events = [];
    }

    protected function recordThat(AggregateChanged $occur): void
    {
        $this->events[] = $occur;
    }

    public function popEvents(): EventCollection
    {
        $appliedEvents = [];
        foreach ($this->events as $key => $event) {
            $this->apply($event);
            unset($this->events[$key]);
            $appliedEvents[] = $event;
        }

        return EventCollection::createImmutableCollection($appliedEvents);
    }

    public function apply(AggregateChanged $aggregateChanged): void
    {
        $eventClass = get_class($aggregateChanged);
        if (array_key_exists($eventClass, static::EVENT_MAP)) {
            $method = static::EVENT_MAP[$eventClass];
            $this->{$method}($aggregateChanged);
        }
    }
}
