<?php

namespace Trappar\AliceGenerator\DataStorage;

use Doctrine\Common\Collections\ArrayCollection;
use Trappar\AliceGenerator\Persister\PersisterInterface;

abstract class AbstractSubdividedCollection
{
    /**
     * @var PersisterInterface
     */
    private $persister;

    /**
     * @var array
     */
    private $stores = [];

    public function setPersister(PersisterInterface $persister): void
    {
        $this->persister = $persister;
    }

    public function getStore(object $object): ArrayCollection
    {
        $subdivision = $this->determineSubdivision($object);

        if (!isset($this->stores[$subdivision])) {
            $this->stores[$subdivision] = $this->getBackingStore();
        }

        return $this->stores[$subdivision];
    }

    protected function determineSubdivision(object $object): string
    {
        return $this->persister->getClass($object);
    }

    protected function getBackingStore(): ArrayCollection
    {
        return new ArrayCollection();
    }
}