<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Event;

use Generator;

class DomainEventEmitter
{
    private static ?self $instance = null;
    private EventCollection $events;

    private function __construct()
    {
        $this->resetEvents();
    }

    private function resetEvents(): void
    {
        $this->events = EventCollection::createEmptyCollection();
    }

    public static function recordThat(AggregateChanged $event): void
    {
        $self = self::assertInstance();
        $self->events->addItem($event);
    }

    public static function emit(): Generator
    {
        $self = self::assertInstance();
        $events = $self->events;
        $self->resetEvents();

        yield from $events;
    }

    private static function assertInstance(): self
    {
        if (null === static::$instance) {
            static::$instance = new self;
        }

        return static::$instance;
    }
}
