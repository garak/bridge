<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Wins;

final class WinsTest extends TestCase
{
    public function testNorthWinsAlias(): void
    {
        $wins = new Wins();
        $new = $wins->northWins();
        self::assertEquals(1, $new->getNorthSouth());
    }

    public function testWestWinsAlias(): void
    {
        $wins = new Wins();
        $new = $wins->westWins();
        self::assertEquals(1, $new->getEastWest());
    }
}
