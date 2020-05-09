<?php

declare(strict_types=1);

namespace Antidot\EventSource\Application\Bus;

interface CommandBus
{
    public function __invoke(object $command): void;
}
