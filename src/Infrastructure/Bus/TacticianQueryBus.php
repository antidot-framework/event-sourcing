<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Bus;

use Antidot\EventSource\Application\Bus\QueryBus;
use Antidot\Tactician\QueryBus as TacticianQueryBusAdapter;

class TacticianQueryBus implements QueryBus
{
    private TacticianQueryBusAdapter $queryBus;

    public function __construct(TacticianQueryBusAdapter $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function __invoke(object $query): object
    {
        return $this->queryBus->handle($query);
    }
}
