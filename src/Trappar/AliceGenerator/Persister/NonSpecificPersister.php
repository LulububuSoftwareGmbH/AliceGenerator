<?php

namespace Trappar\AliceGenerator\Persister;

use Trappar\AliceGenerator\DataStorage\ValueContext;

class NonSpecificPersister extends AbstractPersister
{
    /**
     * @inheritDoc
     */
    public function isObjectManagedByPersister(object $object): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function preProcess(object $object): void
    {
    }

    /**
     * @inheritDoc
     */
    public function isPropertyNoOp(ValueContext $context): bool
    {
        return false;
    }
}