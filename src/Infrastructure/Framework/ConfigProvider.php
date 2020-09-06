<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Framework;

use Antidot\EventSource\Application\Bus\CommandBus;
use Antidot\EventSource\Application\Bus\QueryBus;
use Antidot\EventSource\Infrastructure\Bus\TacticianCommandBus;
use Antidot\EventSource\Infrastructure\Bus\TacticianDbalTransactionalMiddleware;
use Antidot\EventSource\Infrastructure\Bus\TacticianDomainEventDispatcherMiddleware;
use Antidot\EventSource\Infrastructure\Bus\TacticianQueryBus;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'invokables' => [
                    CommandBus::class => TacticianCommandBus::class,
                    TacticianDbalTransactionalMiddleware::class => TacticianDbalTransactionalMiddleware::class,
                    TacticianDomainEventDispatcherMiddleware::class => TacticianDomainEventDispatcherMiddleware::class,
                ],
                'conditionals' => [
                    QueryBus::class => [
                        'class' => TacticianQueryBus::class,
                    ],
                ]
            ]
        ];
    }
}
