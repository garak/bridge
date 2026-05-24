<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Side;
use Garak\Card\Suit;

final class GameAuctionSideTest extends TestCase
{
    public function testAddAuctionMustBeGreater(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        // first a valid bid
        new StubAuction($game, 1, 4, Suit::Diamonds);
        $currentSide = $game->getCurrentSide();

        try {
            // second is lower than previous -> should throw
            new StubAuction($game, 2, 2, Suit::Diamonds);
            self::fail('Expected DomainException was not thrown.');
        } catch (\DomainException $exception) {
            self::assertSame('Auction must be greater than previous one.', $exception->getMessage());
        }

        self::assertCount(1, $game->getAuctions());
        self::assertEquals($currentSide->value, $game->getCurrentSide()->value);
    }

    public function testGetAuctionSideWhenMateProposesSameSuit(): void
    {
        $table = self::getTable();
        $game = new StubGame($table, Side::North);
        // ensure dummy can be assigned to W
        $game->join(new StubPlayer('Gary'), Side::West);

        // orders: 1..7; East (mate of West) proposes diamonds before West wins with diamonds
        new StubAuction($game, 1, 1, Suit::Clubs); // side N
        new StubAuction($game, 2, 2, Suit::Diamonds); // side E (mate)
        new StubAuction($game, 3, null, null); // S pass
        new StubAuction($game, 4, 3, Suit::Diamonds); // W (last valid)
        new StubAuction($game, 5, null, null); // N pass
        new StubAuction($game, 6, null, null); // E pass
        new StubAuction($game, 7, null, null); // S pass -> auction ends

        // last valid auction should be 3d
        self::assertEquals('3d', (string) $game->getAuction());
        // dummy side should be W (opposing of selected side E)
        self::assertEquals('W', $game->getDummySide()?->value);
        // current side should be the next of selected side (E->South)
        self::assertEquals('South', $game->getCurrentSide()->getName());
        self::assertTrue(null !== $game->getTrump() && $game->getTrump()->isEqual(Suit::Diamonds));
    }
}
