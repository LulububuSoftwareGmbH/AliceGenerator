<?php

namespace Trappar\AliceGenerator;

use InvalidArgumentException;
use Metadata\MetadataFactoryInterface;
use ReflectionClass;
use ReflectionProperty;
use Trappar\AliceGenerator\DataStorage\PersistedObjectCache;
use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\Exception\InvalidPropertyNameException;
use Trappar\AliceGenerator\Exception\UnknownObjectTypeException;
use Trappar\AliceGenerator\Metadata\PropertyMetadata;
use Trappar\AliceGenerator\Metadata\Resolver\MetadataResolverInterface;
use Trappar\AliceGenerator\Persister\PersisterInterface;
use Trappar\AliceGenerator\PropertyNamer\PropertyNamerInterface;

class ValueVisitor
{

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var PersistedObjectCache
     */
    private $persistedObjectCache;

    /**
     * @var PersisterInterface
     */
    private $persister;

    /**
     * @var MetadataResolverInterface
     */
    private $metadataResolver;

    /**
     * @var ObjectHandlerRegistryInterface
     */
    private $objectHandlerRegistry;

    /**
     * @var PropertyNamerInterface
     */
    private $propertyNamer;

    /**
     * @var FixtureGenerationContext
     */
    private $fixtureGenerationContext;

    /**
     * @var array
     */
    private $results;

    /**
     * @var int
     */
    private $recursionDepth;

    /**
     * @var bool
     */
    private $strictTypeChecking;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        PersisterInterface $persister,
        MetadataResolverInterface $metadataResolver,
        ObjectHandlerRegistryInterface $objectHandlerRegistry,
        PropertyNamerInterface $propertyNamer,
        bool $strictTypeChecking
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->persister = $persister;
        $this->metadataResolver = $metadataResolver;
        $this->objectHandlerRegistry = $objectHandlerRegistry;
        $this->propertyNamer = $propertyNamer;
        $this->strictTypeChecking = $strictTypeChecking;
    }

    public function setup(FixtureGenerationContext $fixtureGenerationContext): void
    {
        $this->fixtureGenerationContext = $fixtureGenerationContext;

        // Reset caches
        $this->results = [];
        $this->persistedObjectCache = new PersistedObjectCache();
        $this->persistedObjectCache->setPersister($this->persister);
        $this->fixtureGenerationContext->getPersistedObjectConstraints()->setPersister($this->persister);
    }

    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * @param mixed $value
     */
    public function visitSimpleValue($value): ValueContext
    {
        $valueContext = new ValueContext($value);
        $this->visitUnknownType($valueContext);

        return $valueContext;
    }

    public function visitUnknownType(ValueContext $valueContext): void
    {
        if (is_array($valueContext->getValue())) {
            $this->visitArray($valueContext);
        } else if (is_object($valueContext->getValue())) {
            $this->visitObject($valueContext);
        }
    }

    public function visitArray(ValueContext $valueContext): void
    {
        $array = $valueContext->getValue();

        foreach ($array as $key => &$item) {
            $itemValueContext = $this->visitSimpleValue($item);

            if ($itemValueContext->isSkipped()) {
                unset($array[$key]);
            } else {
                $array[$key] = $itemValueContext->getValue();
            }
        }

        if (!count($array)) {
            $valueContext->setSkipped(true);
        } else {
            $valueContext->setValue($array);
        }
    }

    public function visitObject(ValueContext $valueContext): void
    {
        $object = $valueContext->getValue();

        $objectHandled = $this->objectHandlerRegistry->runHandlers($valueContext);

        if (!$objectHandled && $this->persister->isObjectManagedByPersister($object)) {
            if (!$this->fixtureGenerationContext->getPersistedObjectConstraints()->checkValid($object)) {
                $valueContext->setSkipped(true);

                return;
            }

            $result = $this->persistedObjectCache->find($object);
            $referenceNamer = $this->fixtureGenerationContext->getReferenceNamer();

            switch ($result) {
                case PersistedObjectCache::OBJECT_NOT_FOUND:
                    if ($this->recursionDepth <= $this->fixtureGenerationContext->getMaximumRecursion()) {
                        $key = $this->persistedObjectCache->add($object);
                        $reference = $referenceNamer->createReference($object, $key);

                        $objectAdded = $this->handlePersistedObject($object, $reference);

                        if ($objectAdded) {
                            $valueContext->setValue('@' . $reference);

                            return;
                        } else {
                            $this->persistedObjectCache->skip($object);
                            $valueContext->setSkipped(true);

                            return;
                        }
                    }
                    break;
                case PersistedObjectCache::OBJECT_SKIPPED:
                    $valueContext->setSkipped(true);

                    return;
                default:
                    $valueContext->setValue('@' . $referenceNamer->createReference($object, $result));

                    return;
            }

            $valueContext->setSkipped(true);
        }

        if (!$valueContext->isSkipped() && !$valueContext->isModified()) {
            throw new UnknownObjectTypeException(sprintf(
                'Object of unknown type "%s" encountered during generation. Unknown types can\'t be serialized ' .
                'directly. You can create an ObjectHandler for this type, or supply metadata on the property for' .
                'how this should be handled.',
                get_class($valueContext->getValue())
            ));
        }
    }

    /**
     * @param       $object
     * @param       $reference
     *
     * @return bool if the object was added to the object cache
     * @throws \Exception
     */
    private function handlePersistedObject(object $object, string $reference): bool
    {
        $class = $this->persister->getClass($object);
        $this->persister->preProcess($object);
        $classMetadata = $this->metadataFactory->getMetadataForClass($class);

        // Create a new instance of this class to check values against
        $classReflection = new ReflectionClass($classMetadata->name);
        $newObject = $classReflection->newInstanceWithoutConstructor();

        $saveValues = [];
        $this->recursionDepth++;

        foreach ($classMetadata->propertyMetadata as $propertyMetadata) {
            $propertyReflection = new ReflectionProperty($propertyMetadata->class, $propertyMetadata->name);
            $propertyReflection->setAccessible(true);
            $value = $propertyReflection->getValue($object);

            /** @var PropertyMetadata $propertyMetadata */
            $valueContext = new ValueContext($value, $class, $object, $propertyMetadata, $this);

            if ($this->persister->isPropertyNoOp($valueContext)) {
                continue;
            }

            $this->metadataResolver->resolve($valueContext);

            if (!$valueContext->isModified() && !$valueContext->isSkipped()) {
                $value = $valueContext->getValue();

                if (is_null($value)) {
                    continue;
                }

                if ($this->fixtureGenerationContext->isExcludeDefaultValuesEnabled()) {
                    $initialValue = $propertyReflection->getValue($newObject);

                    // Avoid setting unnecessary data
                    if ($this->strictTypeChecking || is_null($value) || is_bool($value) || is_object($value)) {
                        if ($value === $initialValue) {
                            continue;
                        }
                    } else {
                        if ($value == $initialValue) {
                            continue;
                        }
                    }
                }

                $this->visitUnknownType($valueContext);
            }
            if ($valueContext->isSkipped()) {
                continue;
            }

            $propName = $this->propertyNamer->createName($valueContext);
            if (!is_string($propName) || empty($propName)) {
                throw new InvalidPropertyNameException('Property name must be a non empty string.');
            }

            $saveValues[$propName] = $valueContext->getValue();
        }

        $this->recursionDepth--;

        if (!count($saveValues)) {
            return false;
        } else {
            $this->results[$class][$reference] = $saveValues;

            return true;
        }
    }

    private function property(ReflectionClass $reflection, string $name): ReflectionProperty
    {
        do {
            if (!$reflection->hasProperty($name)) {
                continue;
            }

            return $reflection->getProperty($name);
        } while ($reflection = $reflection->getParentClass());

        $reflectionName = $reflection->getName();
        throw new InvalidArgumentException(sprintf('Property %s does not exist in %s', $name, $reflectionName));
    }
}
