<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Repository;

use Antidot\EventSource\Domain\Event\AggregateChanged;
use Antidot\EventSource\Domain\Model\Aggregate\AggregateRoot;
use Antidot\EventSource\Domain\Model\ValueObject\AggregateRootId;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use function get_class;
use function json_encode;

abstract class DbalAggregateRootSingleTableRepository
{
    protected Connection $connection;
    protected string $streamName;

    public function __construct(Connection $connection, string $streamName)
    {
        $this->connection = $connection;
        $this->streamName = $streamName;
    }

    protected function getAggregate(AggregateRootId $aggregateRootId, string $aggregateClass): ?AggregateRoot
    {
        $tableName = $this->streamName;
        if (false === $this->tableExist($tableName)) {
            return null;
        }

        $statement = $this->connection->prepare(<<<SQL
            SELECT * FROM 
                `$tableName` 
            WHERE 
                aggregate_id = :aggregate_id 
            ORDER BY  
                no ASC;
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
        $tableName = $this->streamName;
        $this->createAggregateStream($tableName);
        $this->connection->beginTransaction();
        try {
            /** @var AggregateChanged $event */
            foreach ($aggregateRoot->popEvents() as $event) {
                $this->connection->insert($tableName, [
                    'event_id' => $event->eventId(),
                    'event_class' => get_class($event),
                    'aggregate_id' => $event->aggregateId(),
                    'aggregate_class' => get_class($aggregateRoot),
                    'payload' => json_encode($event->payload(), JSON_THROW_ON_ERROR),
                    'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s'),
                    'version' => $event->version(),
                ]);
            }
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }
    }

    protected function createAggregateStream(string $tableName): void
    {
        if ($this->tableExist($tableName)) {
            return;
        }

        $statement = $this->connection->prepare(<<<SQL
            CREATE TABLE `$tableName` (
              `no` BIGINT(20) NOT NULL AUTO_INCREMENT,
              `event_id` VARCHAR(36) NOT NULL,
              `event_class` CHAR(180) NOT NULL,
              `aggregate_id` VARCHAR(36) NOT NULL,
              `aggregate_class` CHAR(180) NOT NULL,
              `payload` JSON,
              `occurred_on` DATETIME NOT NULL,
              `version` INT(3) NOT NULL,
              PRIMARY KEY (`no`),
              UNIQUE KEY `ix_rsn` (`event_id`),
              KEY `ix_aggregate_id` (`aggregate_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
        SQL
        );
        $statement->execute();
    }

    private function tableExist(string $tableName): bool
    {
        $statement = $this->connection->prepare(<<<SQL
            SELECT * FROM 
                information_schema.tables t
            WHERE 
                t.table_schema = :db_name 
                AND t.table_name = :aggregate_stream
            LIMIT 1;
        SQL
        );

        $statement->bindValue(':db_name', $this->connection->getDatabase());
        $statement->bindValue(':aggregate_stream', $tableName);
        $statement->execute();

        /** @var array|false $result */
        $result = $statement->fetch();

        return false === empty($result);
    }
}
