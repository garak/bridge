<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Side;
use PHPUnit\Framework\TestCase;

final class SideTest extends TestCase
{
    public function testInvalidSuit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid side: foo.');
        new Side('foo');
    }

    public function testGetNext(): void
    {
        self::assertTrue((new Side('N'))->getNext()->is('E'));
        self::assertTrue((new Side('E'))->getNext()->is('S'));
        self::assertTrue((new Side('S'))->getNext()->is('W'));
        self::assertTrue((new Side('W'))->getNext()->is('N'));
    }

    public function testGetOpposing(): void
    {
        self::assertTrue((new Side('N'))->getOpposing()->is('S'));
        self::assertTrue((new Side('E'))->getOpposing()->is('W'));
        self::assertTrue((new Side('S'))->getOpposing()->is('N'));
        self::assertTrue((new Side('W'))->getOpposing()->is('E'));
    }
}
