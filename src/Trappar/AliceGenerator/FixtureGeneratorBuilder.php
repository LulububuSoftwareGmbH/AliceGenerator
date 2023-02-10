<?php

namespace Trappar\AliceGenerator;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Metadata\MetadataFactory;
use Trappar\AliceGenerator\Builder\DefaultMetadataDriverFactory;
use Trappar\AliceGenerator\Builder\MetadataDriverFactoryInterface;
use Trappar\AliceGenerator\Exception\InvalidArgumentException;
use Trappar\AliceGenerator\Metadata\Resolver\Faker\ArrayFakerResolver;
use Trappar\AliceGenerator\Metadata\Resolver\Faker\CallbackFakerResolver;
use Trappar\AliceGenerator\Metadata\Resolver\Faker\NoArgFakerResolver;
use Trappar\AliceGenerator\Metadata\Resolver\Faker\ValueAsArgFakerResolver;
use Trappar\AliceGenerator\Metadata\Resolver\MetadataResolver;
use Trappar\AliceGenerator\Metadata\Resolver\MetadataResolverInterface;
use Trappar\AliceGenerator\ObjectHandler\CollectionHandler;
use Trappar\AliceGenerator\ObjectHandler\DateTimeHandler;
use Trappar\AliceGenerator\Persister\NonSpecificPersister;
use Trappar\AliceGenerator\Persister\PersisterInterface;
use Trappar\AliceGenerator\PropertyNamer\PropertyNamer;
use Trappar\AliceGenerator\PropertyNamer\PropertyNamerInterface;

class FixtureGeneratorBuilder
{
    /**
     * @var array
     */
    private $metadataDirs = [];
    /**
     * @var MetadataDriverFactoryInterface
     */
    private $metadataDriverFactory;
    /**
     * @var PersisterInterface
     */
    private $persister;
    /**
     * @var Reader
     */
    private $annotationReader;
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
     * @var bool
     */
    private $objectHandlersConfigured;
    /**
     * @var YamlWriterInterface
     */
    private $yamlWriter;
    /**
     * @var bool
     */
    private $strictTypeChecking = true;

    public static function create(): self
    {
        /** @phpstan-ignore-next-line */
        return new static();
    }

    public function __construct()
    {
        $this
            ->setMetadataDriverFactory(new DefaultMetadataDriverFactory())
            ->setPersister(new NonSpecificPersister())
            ->setAnnotationReader(new AnnotationReader())
            ->setMetadataResolver(
                new MetadataResolver([
                    new ArrayFakerResolver(),
                    new CallbackFakerResolver(),
                    new ValueAsArgFakerResolver(),
                    new NoArgFakerResolver()
                ])
            )
            ->setObjectHandlerRegistry(new ObjectHandlerRegistry())
            ->setPropertyNamer(new PropertyNamer())
            ->setYamlWriter(new YamlWriter(3, 4));
    }

    /**
     * Adds a directory where the FixtureGenerator will look for class metadata.
     *
     * See: doc/configuration.md
     */
    public function addMetadataDir(string $dir, string $namespacePrefix = ''): self
    {
        if (!is_dir($dir)) {
            throw new InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        $this->metadataDirs[$namespacePrefix] = $dir;

        return $this;
    }

    /**
     * Adds a map of namespace prefixes to directories.
     *
     * @param array<string, string> $namespacePrefixToDirMap
     */
    public function addMetadataDirs(array $namespacePrefixToDirMap): self
    {
        foreach ($namespacePrefixToDirMap as $prefix => $dir) {
            $this->addMetadataDir($dir, $prefix);
        }
        return $this;
    }

    public function setMetadataDriverFactory(DefaultMetadataDriverFactory $metadataDriverFactory): FixtureGeneratorBuilder
    {
        $this->metadataDriverFactory = $metadataDriverFactory;

        return $this;
    }

    public function setPersister(PersisterInterface $persister): FixtureGeneratorBuilder
    {
        $this->persister = $persister;

        return $this;
    }

    public function setAnnotationReader(Reader $annotationReader): FixtureGeneratorBuilder
    {
        $this->annotationReader = $annotationReader;

        return $this;
    }

    public function setMetadataResolver(MetadataResolver $metadataResolver): FixtureGeneratorBuilder
    {
        $this->metadataResolver = $metadataResolver;

        return $this;
    }

    public function configureMetadataResolver(\Closure $closure): self
    {
        $closure($this->metadataResolver);

        return $this;
    }

    public function setObjectHandlerRegistry(ObjectHandlerRegistryInterface $objectHandlerRegistry): FixtureGeneratorBuilder
    {
        $this->objectHandlerRegistry    = $objectHandlerRegistry;

        return $this;
    }

    public function setPropertyNamer(PropertyNamerInterface $propertyNamer): FixtureGeneratorBuilder
    {
        $this->propertyNamer = $propertyNamer;

        return $this;
    }

    public function addDefaultObjectHandlers(): self
    {
        $this->objectHandlersConfigured = true;
        $this->objectHandlerRegistry->registerHandlers([
            new CollectionHandler(),
            new DateTimeHandler(),
        ]);

        return $this;
    }

    public function configureObjectHandlerRegistry(\Closure $closure): self
    {
        $this->objectHandlersConfigured = true;
        $closure($this->objectHandlerRegistry);

        return $this;
    }

    public function setYamlWriter(YamlWriterInterface $yamlWriter): self
    {
        $this->yamlWriter = $yamlWriter;

        return $this;
    }

    public function setStrictTypeChecking(bool $enabled): self
    {
        $this->strictTypeChecking = $enabled;

        return $this;
    }

    public function build(): FixtureGenerator
    {
        $metadataFactory = new MetadataFactory(
            $this->metadataDriverFactory->createDriver($this->metadataDirs, $this->annotationReader)
        );

        if (!$this->objectHandlersConfigured) {
            $this->addDefaultObjectHandlers();
        }

        return new FixtureGenerator(
            new ValueVisitor(
                $metadataFactory,
                $this->persister,
                $this->metadataResolver,
                $this->objectHandlerRegistry,
                $this->propertyNamer,
                $this->strictTypeChecking
            ),
            $this->yamlWriter
        );
    }
}
