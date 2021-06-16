<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Side;
use Garak\Card\Suit;

final class AuctionTest extends TestCase
{
    public function testGreaterWithSameSuit(): void
    {
        $game = new StubGame(self::getTable(), new Side('N'));
        $auction1 = new StubAuction($game, 1, 2, new Suit('d'));
        $auction2 = new StubAuction($game, 2, 4, new Suit('d'));
        self::assertTrue($auction2->isGreaterThan($auction1));
        self::assertCount(2, $game->getAuctions());
        self::assertSame($game, $auction1->getGame());
    }

    public function testGreaterWithDifferentSuits(): void
    {
        $game = new StubGame(self::getTable(), new Side('N'));
        $auction1 = new StubAuction($game, 1, 6, new Suit('c'));
        // diamonds is more than clubs
        $auction2 = new StubAuction($game, 2, 5, new Suit('d'));
        self::assertTrue($auction2->isGreaterThan($auction1));
        // hearts is more than diamonds
        $auction3 = new StubAuction($game, 3, 4, new Suit('h'));
        self::assertTrue($auction3->isGreaterThan($auction2));
        // spades is more than hearts
        $auction4 = new StubAuction($game, 4, 3, new Suit('s'));
        self::assertTrue($auction4->isGreaterThan($auction3));
        // no-trump is more than spades
        $auction5 = new StubAuction($game, 5, 2, null);
        self::assertTrue($auction5->isGreaterThan($auction4));
    }
}
