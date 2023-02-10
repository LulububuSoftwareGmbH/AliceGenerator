<?php

namespace Trappar\AliceGenerator\ObjectHandler;

use Trappar\AliceGenerator\DataStorage\ValueContext;

interface ObjectHandlerInterface
{
    /**
     * Returns true if the handler changed the value, false otherwise.
     */
    public function handle(ValueContext $valueContext): bool;
}