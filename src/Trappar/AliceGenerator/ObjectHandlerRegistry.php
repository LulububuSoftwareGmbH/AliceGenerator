<?php

namespace Trappar\AliceGenerator;

use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\ObjectHandler\ObjectHandlerInterface;

class ObjectHandlerRegistry implements ObjectHandlerRegistryInterface
{
    /**
     * @var ObjectHandlerInterface[]
     */
    protected $handlers = [];

    public function __construct(array $handlers = [])
    {
        $this->registerHandlers($handlers);
    }

    /**
     * @inheritdoc
     */
    public function registerHandlers(array $handlers): void
    {
        foreach ($handlers as $handler) {
            $this->registerHandler($handler);
        }
    }

    public function registerHandler(ObjectHandlerInterface $handler): void
    {
        array_unshift($this->handlers, $handler);
    }

    /**
     * @inheritdoc
     */
    public function runHandlers(ValueContext $valueContext): bool
    {
        foreach ($this->handlers as $handler) {
            if ($handler->handle($valueContext)) {
                return true;
            }
        }

        return false;
    }
}