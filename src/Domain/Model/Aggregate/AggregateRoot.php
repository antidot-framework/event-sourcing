<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Model\Aggregate;

use Antidot\EventSource\Domain\Event\AggregateChanged;
use Antidot\EventSource\Domain\Event\DomainEventEmitter;
use Antidot\EventSource\Domain\Event\EventCollection;

abstract class AggregateRoot
{
    public const EVENT_MAP = [];
    protected array $events;
    protected string $aggregateId;

    final public function __construct()
    {
        $this->events = [];
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    protected function recordThat(AggregateChanged $occur): void
    {
        $this->events[] = $occur;
        DomainEventEmitter::recordThat($occur);
    }

    public function popEvents(): EventCollection
    {
        $appliedEvents = [];
        /**
         * @var int $key
         * @var AggregateChanged $event
         */
        foreach ($this->events as $key => $event) {
            $this->apply($event);
            unset($this->events[$key]);
            $appliedEvents[] = $event;
        }

        return EventCollection::createImmutableCollection($appliedEvents);
    }

    public function apply(AggregateChanged $aggregateChanged): void
    {
        /** @var array<string, string>  */
        $eventMap = static::EVENT_MAP;
        $eventClass = get_class($aggregateChanged);
        if (array_key_exists($eventClass, $eventMap)) {
            $method = $eventMap[$eventClass];
            $this->{$method}($aggregateChanged);
        }
    }
}
