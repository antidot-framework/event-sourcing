<?php

declare(strict_types=1);

namespace Antidot\EventSource\Domain\Model\Collection;

use Countable;
use Generator;
use InvalidArgumentException;
use IteratorAggregate;
use RuntimeException;

abstract class Collection implements Countable, IteratorAggregate
{
    protected string $fqcn;
    protected array $items = [];
    protected bool $locked = false;

    protected function __construct(array $items = [], bool $locked = true)
    {
        $this->setFqcn();
        foreach ($items as $item) {
            $this->addItem($item);
        }
        $this->locked = $locked;
    }

    public static function createImmutableCollection(array $items)
    {
        return new static($items);
    }

    public static function createMutableCollection(array $items = [])
    {
        return new static($items, false);
    }

    public static function createEmptyCollection()
    {
        return new static([], false);
    }

    public function addItem(object $item): void
    {
        $this->assertIsNotLocked();
        $this->assertInstanceOf($item);
        $this->items[] = $item;
    }

    public function lock(): void
    {
        $this->locked = true;
    }

    public function count(): int
    {
        return count($this->items);
    }

    private function assertInstanceOf(object $object): void
    {
        if (false === $object instanceof $this->fqcn) {
            throw new InvalidArgumentException(sprintf(
                'Collection item must be instance of %s class. An instance of %s given.',
                get_class($object),
                $this->fqcn
            ));
        }
    }

    private function assertIsNotLocked(): void
    {
        if (true === $this->locked) {
            throw new RuntimeException('Cannot add new items to locked collection.');
        }
    }

    public function getIterator(): Generator
    {
        yield from $this->items;
    }


    abstract protected function setFqcn(): void;
}
