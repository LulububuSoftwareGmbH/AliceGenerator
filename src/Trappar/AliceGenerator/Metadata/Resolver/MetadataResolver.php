<?php

namespace Trappar\AliceGenerator\Metadata\Resolver;

use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\Exception\FakerResolverException;
use Trappar\AliceGenerator\Metadata\Resolver\Faker\FakerResolverInterface;

class MetadataResolver extends AbstractMetadataResolver
{
    /**
     * @var FakerResolverInterface[]
     */
    protected $fakerResolvers = [];

    public function __construct(array $resolvers = [])
    {
        $this->addFakerResolvers($resolvers);
    }

    public function addFakerResolvers(array $fakerResolvers): void
    {
        foreach ($fakerResolvers as $fakerResolver) {
            $this->addFakerResolver($fakerResolver);
        }
    }

    public function addFakerResolver(FakerResolverInterface $fakerResolver): void
    {
        $this->fakerResolvers[$fakerResolver->getType()] = $fakerResolver;
    }

    /**
     * @throws FakerResolverException
     */
    public function validate(ValueContext $valueContext): void
    {
        if ($valueContext->getMetadata()->fakerName) {
            $type = $valueContext->getMetadata()->fakerResolverType;
            if (!isset($this->fakerResolvers[$type])) {
                throw new FakerResolverException($valueContext, sprintf(
                    'No faker resolver registered with type "%s". %s',
                    $type,
                    $this->getAvailableFakerResolverTypes()
                ));
            }
        }
    }

    public function handle(ValueContext $valueContext)
    {
        if ($valueContext->getMetadata()->ignore) {
            $valueContext->setSkipped(true);
        } elseif (!is_null($valueContext->getMetadata()->staticData)) {
            $valueContext->setValue($valueContext->getMetadata()->staticData);
        } elseif ($valueContext->getMetadata()->fakerName || $valueContext->getMetadata()->fakerResolverType) {
            $this->fakerResolvers[$valueContext->getMetadata()->fakerResolverType]->resolve($valueContext);
        }
    }

    private function getAvailableFakerResolverTypes(): string
    {
        if (count($types = array_keys($this->fakerResolvers))) {
            $typesFormatted = array_map(function ($type) {
                return "\"$type\"";
            }, $types);

            return 'Available types are: ' . implode(', ', $typesFormatted);
        } else {
            return 'No faker resolver types are currently registered for use.';
        }
    }
}