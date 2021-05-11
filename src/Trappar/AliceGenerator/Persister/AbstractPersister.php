<?php

namespace Trappar\AliceGenerator\Persister;

abstract class AbstractPersister implements PersisterInterface
{
    public function getClass(object $object): string
    {
        return get_class($object);
    }
}
