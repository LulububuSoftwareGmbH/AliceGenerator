<?php

namespace Trappar\AliceGenerator\Tests;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGenerator\Exception\FixtureGeneratorBuilderValidationException;
use Trappar\AliceGenerator\FixtureGeneratorBuilder;
use Trappar\AliceGenerator\Tests\Util\FixtureUtils;

class FixtureGeneratorBuilderTest extends TestCase
{
    public function testBuildFixtureGenerator()
    {
        $this->assertInstanceOf('Trappar\AliceGenerator\FixtureGenerator', FixtureUtils::buildFixtureGenerator());
    }
}