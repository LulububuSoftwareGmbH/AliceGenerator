<?php

namespace Trappar\AliceGenerator\DataStorage;

class PersistedObjectConstraints extends AbstractSubdividedCollection
{
    public function add(object $object): void
    {
        $this->getStore($object)->add($object);
    }
    
    public function checkValid(object $object): bool
    {
        $store = $this->getStore($object);
        if ($store->count()) {
            return $store->contains($object);
        }
        
        return true;
    }
}