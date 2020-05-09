<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Bus;

use Antidot\EventSource\Application\Bus\QueryBus;
use League\Tactician\CommandBus;

class TacticianQueryBus implements QueryBus
{
    private CommandBus $queryBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->queryBus = $commandBus;
    }

    public function __invoke(object $command): object
    {
        return $this->queryBus->handle($command);
    }
}
