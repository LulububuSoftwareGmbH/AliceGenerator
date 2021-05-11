<?php

namespace Trappar\AliceGenerator\Persister;

use Trappar\AliceGenerator\DataStorage\ValueContext;

interface PersisterInterface
{
    /**
     * Should return the class for the given object.
     * example: In DoctrinePersister sometimes objects may be proxies which need to be resolved, so a special utility function
     *          must be used to determine an object's class.
     */
    public function getClass(object $object): string;

    /**
     * Should return true if the object is managed by this persister
     */
    public function isObjectManagedByPersister(object $object): bool;

    /**
     * Any code which needs to be immediately run on a persisted object to get the object ready for serialization goes
     * here
     */
    public function preProcess(object $object): void;

    /**
     * Should return true if this property should always be skipped during serialization
     * Example: normally true for IDs
     */
    public function isPropertyNoOp(ValueContext $context): bool;
}