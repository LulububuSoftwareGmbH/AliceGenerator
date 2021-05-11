<?php

namespace Trappar\AliceGenerator\Tests;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGenerator\Exception\InvalidArgumentException;
use Trappar\AliceGenerator\FixtureGeneratorBuilder;
use Trappar\AliceGenerator\Metadata\Resolver\MetadataResolver;
use Trappar\AliceGenerator\ObjectHandlerRegistryInterface;
use Trappar\AliceGenerator\Tests\Util\FixtureUtils;

class FixtureGeneratorBuilderTest extends TestCase
{
    public function testBuildFixtureGenerator(): void
    {
        $this->assertInstanceOf('Trappar\AliceGenerator\FixtureGenerator', FixtureUtils::buildFixtureGenerator());
    }

    public function testConfiguringObjectHandlerRegistry(): void
    {
        FixtureGeneratorBuilder::create()
            ->configureObjectHandlerRegistry(function ($registry) {
                $this->assertInstanceOf(ObjectHandlerRegistryInterface::class, $registry);
            });
    }

    public function testConfiguringMetadataResolver(): void
    {
        FixtureGeneratorBuilder::create()
            ->configureMetadataResolver(function ($registry) {
                $this->assertInstanceOf(MetadataResolver::class, $registry);
            });
    }

    public function testAddInvalidMetadataDir(): void
    {
        $this->expectException(InvalidArgumentException::class);
        FixtureGeneratorBuilder::create()->addMetadataDir('asdf');
    }
}