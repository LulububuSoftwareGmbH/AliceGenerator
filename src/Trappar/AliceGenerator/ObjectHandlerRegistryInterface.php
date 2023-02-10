<?php

namespace Trappar\AliceGenerator;

use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\ObjectHandler\ObjectHandlerInterface;

interface ObjectHandlerRegistryInterface
{
    /**
     * @param ObjectHandlerInterface[] $handlers
     */
    public function registerHandlers(array $handlers): void;

    public function runHandlers(ValueContext $valueContext): bool;
}