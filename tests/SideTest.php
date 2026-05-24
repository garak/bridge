<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Side;
use PHPUnit\Framework\TestCase;

final class SideTest extends TestCase
{
    public function testInvalidSide(): void
    {
        $this->expectException(\ValueError::class);
        Side::from('foo');
    }

    public function testGetNext(): void
    {
        self::assertTrue(Side::North->getNext()->is('E'));
        self::assertTrue(Side::East->getNext()->is('S'));
        self::assertTrue(Side::South->getNext()->is('W'));
        self::assertTrue(Side::West->getNext()->is('N'));
    }

    public function testGetOpposing(): void
    {
        self::assertTrue(Side::North->getOpposing()->is('S'));
        self::assertTrue(Side::East->getOpposing()->is('W'));
        self::assertTrue(Side::South->getOpposing()->is('N'));
        self::assertTrue(Side::West->getOpposing()->is('E'));
    }
}
