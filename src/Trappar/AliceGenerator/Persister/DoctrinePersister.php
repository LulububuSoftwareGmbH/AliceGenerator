<?php

namespace Trappar\AliceGenerator\Persister;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Trappar\AliceGenerator\DataStorage\ValueContext;

class DoctrinePersister extends AbstractPersister
{
    /**
     * @var EntityManagerInterface
     */
    private $om;

    public function __construct(EntityManagerInterface $om)
    {
        $this->om = $om;
    }

    public function getClass(object $object): string
    {
        return ClassUtils::getClass($object);
    }

    public function isObjectManagedByPersister(object $object): bool
    {
        return $this->getMetadata($object) !== false;
    }

    public function preProcess(object $object): void
    {
        // Force proxy objects to load data
        if (method_exists($object, '__load')) {
            $object->__load();
        }
    }

    public function isPropertyNoOp(ValueContext $context): bool
    {
        $classMetadata = $this->getMetadata($context->getContextObject());
        \assert($classMetadata instanceof ClassMetadataInfo);

        $propName = $context->getPropName();

        // Skip ID properties if they are not part of composite ID
        $ignore = false;
        if ($classMetadata->isIdentifier($propName) && $classMetadata->generatorType != ClassMetadataInfo::GENERATOR_TYPE_NONE && !$classMetadata->isIdentifierComposite){
            $ignore = true;
        }

        // Skip unmapped properties
        $mapped = true;
        if ($classMetadata instanceof \Doctrine\ORM\Mapping\ClassMetadata) {
            try {
                $classMetadata->getReflectionProperty($propName);
            } catch (\Exception $e) {
                $mapped = false;
            }
        }

        return $ignore || !$mapped;
    }

    /**
     * @return bool|ClassMetadata|ClassMetadataInfo
     */
    protected function getMetadata(object $object)
    {
        try {
            return $this->om->getClassMetadata($this->getClass($object));
        } catch (\Exception $e) {
            return false;
        }
    }
}