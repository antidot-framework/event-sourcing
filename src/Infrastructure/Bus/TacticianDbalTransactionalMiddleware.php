<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Bus;

use Doctrine\DBAL\Connection;
use League\Tactician\Middleware;
use Throwable;

class TacticianDbalTransactionalMiddleware implements Middleware
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute($command, callable $next)
    {
        $this->connection->beginTransaction();

        try {
            /** @var mixed $result */
            $result = $next($command);
            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollBack();
            throw $exception;
        }

        return $result;
    }
}
