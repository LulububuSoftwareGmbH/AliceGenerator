<?php

namespace Trappar\AliceGenerator\DataStorage;

use Trappar\AliceGenerator\Metadata\PropertyMetadata;
use Trappar\AliceGenerator\ValueVisitor;

class ValueContext
{
    /**
     * @var ValueVisitor
     */
    private $valueVisitor;

    /**
     * @var PropertyMetadata
     */
    private $metadata;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @var object
     */
    private $contextObject;

    /**
     * @var string|object|null
     */
    private $contextObjectClass;

    /**
     * @var bool
     */
    private $modified = false;

    /**
     * @var bool
     */
    private $skipped = false;

    /**
     * @param mixed $value
     * @param string|object|null $contextObjectClass
     */
    public function __construct(
        $value = null,
        $contextObjectClass = null,
        object $contextObject = null,
        PropertyMetadata $metadata = null,
        ValueVisitor $valueVisitor = null
    )
    {
        $this->value              = $value;
        $this->metadata           = $metadata;
        $this->contextObject      = $contextObject;
        $this->contextObjectClass = $contextObjectClass;
        $this->valueVisitor       = $valueVisitor;
    }

    public function getMetadata(): PropertyMetadata
    {
        return $this->metadata;
    }

    public function getValueVisitor(): ValueVisitor
    {
        return $this->valueVisitor;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value, bool $setModified = true): self
    {
        $this->value = $value;
        if ($setModified) {
            $this->modified = true;
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContextObject()
    {
        return $this->contextObject;
    }

    /**
     * @return string|object|null
     */
    public function getContextObjectClass()
    {
        return $this->contextObjectClass;
    }

    /**
     * @return mixed
     */
    public function getPropName()
    {
        return $this->getMetadata()->name;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    public function setSkipped(bool $skipped): void
    {
        $this->skipped = $skipped;
    }
}