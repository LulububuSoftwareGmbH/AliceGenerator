<?php

namespace Trappar\AliceGenerator\Tests\DataStorage;

use PHPUnit\Framework\TestCase;
use Trappar\AliceGenerator\DataStorage\PersistedObjectConstraints;
use Trappar\AliceGenerator\Persister\NonSpecificPersister;
use Trappar\AliceGenerator\Tests\Fixtures\User;

class PersistedObjectConstraintsTest extends TestCase
{
    public function test(): void
    {
        $user1 = new User();
        $user2 = new User();

        $constraints = new PersistedObjectConstraints();
        $constraints->setPersister(new NonSpecificPersister());

        $this->assertTrue($constraints->checkValid($user1));

        $constraints->add($user1);

        $this->assertTrue($constraints->checkValid($user1));
        $this->assertFalse($constraints->checkValid($user2));
    }
}