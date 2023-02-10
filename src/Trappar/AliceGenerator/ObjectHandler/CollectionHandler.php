<?php

namespace Trappar\AliceGenerator\ObjectHandler;

use Trappar\AliceGenerator\DataStorage\ValueContext;

class CollectionHandler implements ObjectHandlerInterface
{
    /**
     * Returns true if the handler changed the value, false otherwise.
     */
    public function handle(ValueContext $valueContext): bool
    {
        if (!is_a($collection = $valueContext->getValue(), 'Doctrine\Common\Collections\Collection')) {
            return false;
        }

        $valueContext->setValue($collection->toArray());
        $valueContext->getValueVisitor()->visitArray($valueContext);

        return true;
    }
}