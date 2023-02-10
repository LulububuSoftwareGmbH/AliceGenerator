<?php

namespace Trappar\AliceGenerator\Builder;

use Doctrine\Common\Annotations\Reader;
use Metadata\Driver\DriverInterface;

interface MetadataDriverFactoryInterface
{
    /**
     * @param array $metadataDirs
     */
    public function createDriver(array $metadataDirs, Reader $annotationReader): DriverInterface;
}