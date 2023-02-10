<?php

namespace Trappar\AliceGenerator\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata
{
    /**
     * @var string|null
     */
    public $staticData;

    /**
     * @var string
     */
    public $fakerName;

    /**
     * @var string
     */
    public $fakerResolverType;

    /**
     * @var array<mixed>
     */
    public $fakerResolverArgs;

    /**
     * @var bool
     */
    public $ignore = false;
}