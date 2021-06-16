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
        $game = new StubGame(self::getTable(), new Side('W'));
        self::assertFalse($player->isPlaying($game));
    }

    public function testIsSide(): void
    {
        $player = new StubPlayer('John Wayne');
        $game = new StubGame(self::getTable(), new Side('W'));
        $game->join($player, new Side('E'));
        self::assertTrue($player->isSide($game, new Side('E')));
        self::assertFalse($player->isSide($game, new Side('W')));
    }
}
