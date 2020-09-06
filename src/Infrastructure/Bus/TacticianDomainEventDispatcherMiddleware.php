<?php

declare(strict_types=1);

namespace Antidot\EventSource\Infrastructure\Bus;

use Antidot\EventSource\Domain\Event\AggregateChanged;
use Antidot\EventSource\Domain\Event\DomainEventEmitter;
use League\Tactician\Middleware;
use Psr\EventDispatcher\EventDispatcherInterface;

class TacticianDomainEventDispatcherMiddleware implements Middleware
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function execute($command, callable $next)
    {
        /** @var mixed $result */
        $result = $next($command);
        /** @var AggregateChanged $event */
        foreach (DomainEventEmitter::emit() as $event) {
            $this->dispatcher->dispatch($event);
        }

        return $result;
    }
}
