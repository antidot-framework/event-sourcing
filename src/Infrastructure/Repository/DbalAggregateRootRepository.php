<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Repository;

use Antidot\EventSource\Domain\Event\AggregateChanged;
use Antidot\EventSource\Domain\Event\DomainEventEmitter;
use Antidot\EventSource\Domain\Model\Aggregate\AggregateRoot;
use Antidot\EventSource\Domain\Model\ValueObject\AggregateRootId;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use function get_class;
use function json_encode;

abstract class DbalAggregateRootRepository
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    protected function getAggregate(AggregateRootId $aggregateRootId, string $aggregateClass): AggregateRoot
    {
        $statement = $this->connection->prepare(<<<SQL
            SELECT * FROM `event_store` WHERE aggregate_id = :aggregate_id ORDER BY no ASC;
            SQL
        );
        $statement->bindValue('aggregate_id', $aggregateRootId->value());
        $statement->execute();
        /** @var null|array $queryResult */
        $queryResult = $statement->fetchAll();
        /** @var AggregateRoot $aggregate */
        $aggregate = new $aggregateClass();

        /** @var array<string, mixed> $item */
        foreach ($queryResult ?? [] as $item) {
            /** @var string $eventClass */
            $eventClass = $item['event_class'];
            /** @var string $payload */
            $payload = $item['payload'];
            /** @var string $occurredOn */
            $occurredOn = $item['occurred_on'];
            /** @var AggregateChanged $event */
            $event = new $eventClass(
                $item['event_id'],
                $item['aggregate_id'],
                json_decode($payload, true, 16, JSON_THROW_ON_ERROR),
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $occurredOn)
            );
            $aggregate->apply($event);
        }

        return $aggregate;
    }

    protected function saveAggregate(AggregateRoot $aggregateRoot): void
    {
        /** @var AggregateChanged $event */
        foreach ($aggregateRoot->popEvents() as $event) {
            $this->connection->insert('event_store', [
                'event_id' => $event->eventId(),
                'event_class' => get_class($event),
                'aggregate_id' => $event->aggregateId(),
                'aggregate_class' => get_class($aggregateRoot),
                'payload' => json_encode($event->payload(), JSON_THROW_ON_ERROR),
                'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s'),
            ]);
            DomainEventEmitter::recordThat($event);
        }
    }
}
