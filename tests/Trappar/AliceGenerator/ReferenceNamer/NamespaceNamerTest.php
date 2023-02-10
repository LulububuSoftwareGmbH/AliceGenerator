<?php

namespace Trappar\AliceGenerator\Tests\ReferenceNamer;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGenerator\ReferenceNamer\NamespaceNamer;
use Trappar\AliceGenerator\Tests\Fixtures\User;

class NamespaceNamerTest extends TestCase
{
    public function testDefault(): void
    {
        $this->assertSame(
            'TrapparAliceGeneratorTestsFixturesUser-',
            $this->getNamer()->createPrefix(new User())
        );
    }

    public function testIgnoredNamespaces(): void
    {
        $this->assertSame(
            'TrapparFixturesUser-',
            $this->getNamer()
                ->setIgnoredNamespaces(['AliceGenerator', 'Tests'])
                ->createPrefix(new User())
        );
    }

    public function testNamespaceSeparator(): void
    {
        $this->assertSame(
            'Trappar-AliceGenerator-Tests-Fixtures-User-',
            $this->getNamer()
                ->setNamespaceSeparator('-')
                ->createPrefix(new User())
        );
    }

    private function getNamer(): NamespaceNamer
    {
        return new NamespaceNamer();
    }
}