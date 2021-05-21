<?php

namespace Trappar\AliceGenerator\Tests\ObjectHandler;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGenerator\DataStorage\ValueContext;
use Trappar\AliceGenerator\ObjectHandler\DateTimeHandler;

class DateTimeHandlerTest extends TestCase
{
    /**
     * @var DateTimeHandler
     */
    private $handler;

    public function setup(): void
    {
        $this->handler = new DateTimeHandler();
    }

    public function testDateOnly(): void
    {
        $valueContext = new ValueContext(new \DateTime('Aug 3, 1955'));

        $this->handler->handle($valueContext);

        $this->assertSame('<(new \DateTime("1955-08-03"))>', $valueContext->getValue());
    }

    public function testDateTime(): void
    {
        $valueContext = new ValueContext(new \DateTime('Aug 3, 1955 12PM', new \DateTimeZone('UTC')));

        $this->handler->handle($valueContext);

        $this->assertSame('<(new \DateTime("1955-08-03 12:00:00", new \DateTimeZone("UTC")))>', $valueContext->getValue());
    }
}