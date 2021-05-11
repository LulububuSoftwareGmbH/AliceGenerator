<?php

namespace Trappar\AliceGenerator\ReferenceNamer;

use Doctrine\Common\Util\ClassUtils;

class NamespaceNamer implements ReferenceNamerInterface
{
    /**
     * @var string
     */
    protected $namespaceSeparator = '';
    /**
     * @var string[]
     */
    protected $ignoredNamespaces = [];

    public function createReference(object $object, int $key): string
    {
        return $this->createPrefix($object).$key;
    }

    public function createPrefix(object $object): string
    {
        $class = ClassUtils::getClass($object);

        $parts          = explode('\\', $class);
        $partCount      = count($parts);
        $namespaceParts = array_slice($parts, 0, $partCount - 1);
        $namespaceParts = array_diff($namespaceParts, $this->ignoredNamespaces);
        $className      = $parts[$partCount - 1];

        return
            implode($this->namespaceSeparator, $namespaceParts) .
            $this->namespaceSeparator .
            $className .
            '-';
    }

    /**
     * @param string[] $ignoredNamespaces
     */
    public function setIgnoredNamespaces(array $ignoredNamespaces): NamespaceNamer
    {
        $this->ignoredNamespaces = $ignoredNamespaces;

        return $this;
    }

    public function setNamespaceSeparator(string $namespaceSeparator): NamespaceNamer
    {
        $this->namespaceSeparator = $namespaceSeparator;

        return $this;
    }
}