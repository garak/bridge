<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Side;
use Garak\Card\Suit;

final class GameAuctionSideTest extends TestCase
{
    public function testAddAuctionMustBeGreater(): void
    {
        $game = new StubGame(self::getTable(), new Side('N'));
        // first a valid bid
        new StubAuction($game, 1, 4, new Suit('d'));
        $currentSide = $game->getCurrentSide();

        try {
            // second is lower than previous -> should throw
            new StubAuction($game, 2, 2, new Suit('d'));
            self::fail('Expected DomainException was not thrown.');
        } catch (\DomainException $exception) {
            self::assertSame('Auction must be greater than previous one.', $exception->getMessage());
        }

        self::assertCount(1, $game->getAuctions());
        self::assertEquals((string) $currentSide, (string) $game->getCurrentSide());
    }

    public function testGetAuctionSideWhenMateProposesSameSuit(): void
    {
        $table = self::getTable();
        $game = new StubGame($table, new Side('N'));
        // ensure dummy can be assigned to W
        $game->join(new StubPlayer('Gary'), new Side('W'));

        // orders: 1..7; East (mate of West) proposes diamonds before West wins with diamonds
        new StubAuction($game, 1, 1, new Suit('c')); // side N
        new StubAuction($game, 2, 2, new Suit('d')); // side E (mate)
        new StubAuction($game, 3, null, null); // S pass
        new StubAuction($game, 4, 3, new Suit('d')); // W (last valid)
        new StubAuction($game, 5, null, null); // N pass
        new StubAuction($game, 6, null, null); // E pass
        new StubAuction($game, 7, null, null); // S pass -> auction ends

        // last valid auction should be 3d
        self::assertEquals('3d', (string) $game->getAuction());
        // dummy side should be W (opposing of selected side E)
        self::assertEquals('W', (string) $game->getDummySide());
        // current side should be the next of selected side (E->South)
        self::assertEquals('South', $game->getCurrentSide()->getName());
        self::assertTrue(null !== $game->getTrump() && $game->getTrump()->isEqual(new Suit('d')));
    }
}
