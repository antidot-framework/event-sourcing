<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Event;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonSerializable;
use Psr\EventDispatcher\StoppableEventInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;

abstract class AggregateChanged implements StoppableEventInterface, JsonSerializable
{
    protected const DATE_FORMAT = 'Y-m-d H:i:s';
    protected const PROPERTIES = [
        'aggregate_id',
        'payload',
        'occurred_on',
    ];
    protected string $eventId;
    protected string $aggregateId;
    protected array $payload;
    protected DateTimeImmutable $occurredOn;
    protected bool $stopped = false;

    final public function __construct(
        string $eventId,
        string $aggregateId,
        array $payload,
        DateTimeImmutable $occurredOn
    ) {
        $this->eventId = $eventId;
        $this->aggregateId = $aggregateId;
        $this->payload = $payload;
        $this->occurredOn = $occurredOn;
    }

    public static function occur(string $aggregateId, array $payload): self
    {
        $now = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, date(self::DATE_FORMAT));
        if (false === $now) {
            throw new RuntimeException('Error ocurred obtaining datetime from server.');
        }

        return new static(
            Uuid::uuid4()->toString(),
            $aggregateId,
            $payload,
            $now
        );
    }

    private static function assertValidSerialisedEvent(array $serializedEvent): void
    {
        foreach (self::PROPERTIES as $property) {
            if (array_key_exists($property, $serializedEvent)) {
                throw new InvalidArgumentException(sprintf(
                    'Property %s is required to reconstitute an event.',
                    $property
                ));
            }
        }
    }

    public function eventId(): string
    {
        return $this->eventId;
    }

    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => static::class,
            'aggregate_id' => $this->aggregateId,
            'payload' => $this->payload,
            'occurred_on' => $this->occurredOn,
        ];
    }
}
