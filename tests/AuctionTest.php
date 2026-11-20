<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Side;
use Garak\Card\Suit;

final class AuctionTest extends TestCase
{
    public function testGreaterWithSameSuit(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $auction1 = new StubAuction($game, 1, 2, Suit::Diamonds);
        $auction2 = new StubAuction($game, 2, 4, Suit::Diamonds);
        self::assertTrue($auction2->isGreaterThan($auction1));
        self::assertCount(2, $game->getAuctions());
        self::assertSame($game, $auction1->getGame());
    }

    public function testGreaterWithDifferentSuits(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $auction1 = new StubAuction($game, 1, 6, Suit::Clubs);
        // diamonds are more than clubs
        $auction2 = new StubAuction($game, 2, 5, Suit::Diamonds);
        self::assertTrue($auction2->isGreaterThan($auction1));
        // hearts are more than diamonds
        $auction3 = new StubAuction($game, 3, 4, Suit::Hearts);
        self::assertTrue($auction3->isGreaterThan($auction2));
        // spades are more than hearts
        $auction4 = new StubAuction($game, 4, 3, Suit::Spades);
        self::assertTrue($auction4->isGreaterThan($auction3));
        // no-trump are more than spades
        $auction5 = new StubAuction($game, 5, 2, null);
        self::assertTrue($auction5->isGreaterThan($auction4));
    }

    public function testIsSameSuitWithBothNull(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $a1 = new StubAuction($game, 1, null, null);
        $a2 = new StubAuction($game, 2, null, null);

        self::assertFalse($a1->isSameSuit($a2));
    }

    public function testIsSameSuitWithOneNull(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $a1 = new StubAuction($game, 1, null, null);
        $a2 = new StubAuction($game, 2, 3, Suit::Diamonds);

        self::assertFalse($a2->isSameSuit($a1));
    }

    public function testGetSuitValueForPassAndNoTrumpBid(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $pass = new StubAuction($game, 1, null, null);
        $noTrump = new StubAuction($game, 2, 1, null);

        $otherGame = new StubGame(self::getTable(), Side::North);
        $clubs = new StubAuction($otherGame, 1, 2, Suit::Clubs);

        self::assertEquals(16, $pass->getSuitValue());
        self::assertEquals(16, $noTrump->getSuitValue());
        self::assertEquals(1, $clubs->getSuitValue());
    }

    public function testNoTrumpComparisonUsesBidValueWhenBothNoTrump(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $oneNoTrump = new StubAuction($game, 1, 1, null);
        $threeNoTrump = new StubAuction($game, 2, 3, null);

        self::assertTrue($threeNoTrump->isGreaterThan($oneNoTrump));
    }
}
