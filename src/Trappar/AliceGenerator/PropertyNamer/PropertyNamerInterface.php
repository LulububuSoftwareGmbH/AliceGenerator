<?php

namespace Trappar\AliceGenerator\PropertyNamer;

use Trappar\AliceGenerator\DataStorage\ValueContext;

interface PropertyNamerInterface
{
    public function createName(ValueContext $context): string;
}
