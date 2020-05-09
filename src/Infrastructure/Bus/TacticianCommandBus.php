<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Bus;

use Antidot\EventSource\Application\Bus\CommandBus as BaseCommandBus;
use League\Tactician\CommandBus;

class TacticianCommandBus implements BaseCommandBus
{
    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(object $command): void
    {
        $this->commandBus->handle($command);
    }
}
