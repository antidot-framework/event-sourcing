<?php

declare(strict_types=1);

namespace Antidot\EventSource\Application\Bus;

interface QueryBus
{
    public function __invoke(object $query): object;
}
