<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Side;

final class PlayerTest extends TestCase
{
    public function testStringPlayer(): void
    {
        $player = new StubPlayer('Liz Taylor');
        self::assertEquals('Liz Taylor', (string) $player);
    }

    public function testIsPlaying(): void
    {
        $player = new StubPlayer('John Wayne');
        $game = new StubGame(self::getTable(), Side::West);
        self::assertFalse($player->isPlaying($game));
    }

    public function testIsSide(): void
    {
        $player = new StubPlayer('John Wayne');
        $game = new StubGame(self::getTable(), Side::West);
        $game->join($player, Side::East);
        self::assertTrue($player->isSide($game, Side::East));
        self::assertFalse($player->isSide($game, Side::West));
    }
}
