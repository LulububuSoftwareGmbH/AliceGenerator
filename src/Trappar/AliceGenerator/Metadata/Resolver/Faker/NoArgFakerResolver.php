<?php

namespace Trappar\AliceGenerator\Metadata\Resolver\Faker;

use Trappar\AliceGenerator\DataStorage\ValueContext;

class NoArgFakerResolver extends AbstractFakerResolver
{
    /**
     * @inheritdoc
     *
     * @return string|null
     */
    public function getType()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function handle(ValueContext $valueContext)
    {
        return [];
    }
}