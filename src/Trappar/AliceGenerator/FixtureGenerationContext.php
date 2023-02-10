<?php

namespace Trappar\AliceGenerator;

use Trappar\AliceGenerator\DataStorage\PersistedObjectConstraints;
use Trappar\AliceGenerator\Exception\InvalidArgumentException;
use Trappar\AliceGenerator\Persister\NonSpecificPersister;
use Trappar\AliceGenerator\ReferenceNamer\ClassNamer;
use Trappar\AliceGenerator\ReferenceNamer\ReferenceNamerInterface;

class FixtureGenerationContext
{
    /**
     * @var int
     */
    private $maximumRecursion = 5;
    /**
     * @var PersistedObjectConstraints
     */
    private $persistedObjectConstraints;
    /**
     * @var ReferenceNamerInterface
     */
    private $referenceNamer;
    /**
     * @var bool
     */
    private $excludeDefaultValues = true;
    /**
     * @var bool
     */
    private $sortResults = true;

    public function __construct()
    {
        $this->referenceNamer             = new ClassNamer();
        $this->persistedObjectConstraints = new PersistedObjectConstraints();
        $this->persistedObjectConstraints->setPersister(new NonSpecificPersister());
    }

    public static function create(): self
    {
        /** @phpstan-ignore-next-line */
        return new static();
    }

    public function getMaximumRecursion(): int
    {
        return $this->maximumRecursion;
    }

    public function setMaximumRecursion(int $max): FixtureGenerationContext
    {
        $this->maximumRecursion = $max;

        return $this;
    }

    /**
     * @param array|mixed $objects
     */
    public function addPersistedObjectConstraint($objects): FixtureGenerationContext
    {
        $objects = is_array($objects) ? $objects : [$objects];

        foreach ($objects as $object) {
            if (!is_object($object)) {
                throw new InvalidArgumentException(sprintf(
                    'Non-object passed to addPersistedObjectConstraint() - "%s" given', gettype($object)
                ));
            }
            $this->getPersistedObjectConstraints()->add($object);
        }

        return $this;
    }

    public function getPersistedObjectConstraints(): PersistedObjectConstraints
    {
        return $this->persistedObjectConstraints;
    }

    public function getReferenceNamer(): ReferenceNamerInterface
    {
        return $this->referenceNamer;
    }

    public function setReferenceNamer(ReferenceNamerInterface $referenceNamer): FixtureGenerationContext
    {
        $this->referenceNamer = $referenceNamer;

        return $this;
    }

    public function isExcludeDefaultValuesEnabled(): bool
    {
        return $this->excludeDefaultValues;
    }

    public function setExcludeDefaultValues(bool $excludeDefaultValues): FixtureGenerationContext
    {
        $this->excludeDefaultValues = $excludeDefaultValues;

        return $this;
    }

    public function isSortResultsEnabled(): bool
    {
        return $this->sortResults;
    }

    public function setSortResults(bool $sortResults): FixtureGenerationContext
    {
        $this->sortResults = $sortResults;

        return $this;
    }
}