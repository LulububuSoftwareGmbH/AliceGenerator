<?php

namespace Trappar\AliceGenerator\Tests\Metadata\Resolver\Faker;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\Exception\FakerResolverException;
use Trappar\AliceGenerator\Metadata\PropertyMetadata;
use Trappar\AliceGenerator\Metadata\Resolver\Faker\CallbackFakerResolver;
use Trappar\AliceGenerator\Tests\Fixtures\User;

class CallbackFakerResolverTest extends TestCase
{
    /**
     * @var string
     */
    private $testProp = 'testProp';

    /**
     * @var CallbackFakerResolver
     */
    private $resolver;

    public function setup(): void
    {
        $this->resolver = new CallbackFakerResolver();
    }

    /**
     * @dataProvider getTestCases
     * @param array $fakerArgs
     */
    public function testResolve(string $expected, array $fakerArgs): void
    {
        $this->assertSame($expected, $this->runResolve($fakerArgs));
    }

    /**
     * @return array<array{string,string[]}>
     */
    public function getTestCases(): array
    {
        return [
            ['foo', [self::class, 'toFixtureString']],
            ['foo', ['toFixtureString']],
            ['testProp', ['toFixtureStringNonStatic']],
            ['<myFaker("bar")>', ['toFixtureArray']],
            ['baz', ['toFixtureValueContext']],
        ];
    }

    public function testInvalidClass(): void
    {
        $this->expectException(FakerResolverException::class);
        $this->expectExceptionMessageMatches('/must be statically callable/');
        $this->runResolve(['invalid_class', 'toFixture']);
    }

    public function testInvalidMethod(): void
    {
        $this->expectException(FakerResolverException::class);
        $this->expectExceptionMessageMatches('/must publicly exist/');
        $this->runResolve(['invalidMethod']);
    }

    public function testTooManyArguments(): void
    {
        $this->expectException(FakerResolverException::class);
        $this->expectExceptionMessageMatches('/can only accept one or two/i');
        $this->runResolve([1,2,3,4]);
    }

    /**
     * @return mixed
     */
    private function runResolve(array $fakerArgs)
    {
        $metadata = new PropertyMetadata(User::class, 'username');
        $metadata->fakerName = 'myFaker';
        $metadata->fakerResolverArgs = $fakerArgs;

        $context = new ValueContext(null, null, $this, $metadata);

        $this->resolver->resolve($context);

        return $context->getValue();
    }

    public static function toFixtureString(): string
    {
        return 'foo';
    }

    public function toFixtureStringNonStatic(): string
    {
        return $this->testProp;
    }

    /**
     * @return string[]
     */
    public static function toFixtureArray(): array
    {
        return ['bar'];
    }

    public static function toFixtureValueContext(ValueContext $context): ValueContext
    {
        $context->setValue('baz');

        return $context;
    }
}