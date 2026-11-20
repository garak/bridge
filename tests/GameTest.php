<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Hand;
use Garak\Bridge\Player;
use Garak\Bridge\Side;
use Garak\Bridge\Table;
use Garak\Bridge\Turn;
use Garak\Card\Card;
use Garak\Card\Suit;

final class GameTest extends TestCase
{
    public function testGameWithLessThan7Auctions(): void
    {
        $table = self::getTable();
        $game = new StubGame($table, Side::North);
        self::assertEquals($table, $game->getStartingTable());
        self::assertEquals($table, $game->getCurrentTable());
        $game->join(new StubPlayer('Gary'), Side::West);
        new StubAuction($game, 1, 3, Suit::Diamonds);
        new StubAuction($game, 2, 4, Suit::Diamonds);
        new StubAuction($game, 3, null, null);
        new StubAuction($game, 4, null, null);
        new StubAuction($game, 5, null, null);
        self::assertTrue(null !== $game->getTrump() && $game->getTrump()->isEqual(Suit::Diamonds));
        self::assertEquals('South', $game->getCurrentSide()->getName());
        self::assertEquals('4d', $game->getAuction());
        self::assertEquals('W', $game->getDummySide()?->value);
        self::assertEquals(Suit::Diamonds, $game->getTrump());
    }

    public function testGameWithMoreThan7Auctions(): void
    {
        $table = self::getTable();
        $game = new StubGame($table, Side::North);
        $game->join(new StubPlayer('Gary'), Side::East);
        new StubAuction($game, 1, 3, Suit::Diamonds);
        new StubAuction($game, 2, 4, Suit::Diamonds);
        new StubAuction($game, 3, null, null);
        new StubAuction($game, 4, 2, Suit::Hearts);
        new StubAuction($game, 5, null, null);
        new StubAuction($game, 6, null, null);
        new StubAuction($game, 7, null, null);
        self::assertTrue(null !== $game->getTrump() && $game->getTrump()->isEqual(Suit::Hearts));
        self::assertEquals('North', $game->getCurrentSide()->getName());
        self::assertEquals('2h', $game->getAuction());
        self::assertEquals('E', $game->getDummySide()?->value);
    }

    public function testPlayRandomGame(): void
    {
        $table = self::getTable();
        $game = new StubGame($table, Side::North);
        self::assertEquals(Side::North, $game->getCurrentSide());
        $first = $table->getNorth()->getRandomCard();
        new Turn($game, 1, $first);
        self::assertEquals(Side::East, $game->getCurrentSide());
        new Turn($game, 2, $table->getEast()->getRandomCard($first->getSuit()));
        self::assertEquals(Side::South, $game->getCurrentSide());
        new Turn($game, 3, $table->getSouth()->getRandomCard($first->getSuit()));
        self::assertEquals(Side::West, $game->getCurrentSide());
        new Turn($game, 4, $table->getWest()->getRandomCard($first->getSuit()));
        self::assertFalse($game->isGameOver());
        self::assertCount(4, $game->getTurns());
    }

    public function testPlayGame(): void
    {
        $north = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $east = Hand::createFromString('9d,Jh,5s,8c,Ks,4s,5h,4d,8s,Jc,2d,2s,Qs');
        $south = Hand::createFromString('7h,Kd,Js,2h,Th,Qh,7s,Ac,3c,Ad,7c,9s,6h');
        $west = Hand::createFromString('9h,Ts,5c,Jd,9c,As,8h,Ah,Kh,8d,4c,Qd,5d');
        $table = new Table($north, $east, $south, $west);
        $game = new StubGame($table, Side::North);
        new Turn($game, 1, Card::fromRankSuit('6s'));
        new Turn($game, 2, Card::fromRankSuit('4s'));
        new Turn($game, 3, Card::fromRankSuit('7s'));
        new Turn($game, 4, Card::fromRankSuit('Ts'));
        self::assertEquals(1, $game->getWins()->getEastWest());
        self::assertEquals(0, $game->getWins()->getNorthSouth());
        $hand = Hand::createFromString('9h,5c,Jd,9c,As,8h,Ah,Kh,8d,4c,Qd,5d', false);
        self::assertEquals((string) $hand, (string) $game->getCurrentHand());
    }

    public function testPlayInvalidCard(): void
    {
        $north = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $east = Hand::createFromString('9d,Jh,5s,8c,Ks,4s,5h,4d,8s,Jc,2d,2s,Qs');
        $south = Hand::createFromString('7h,Kd,Js,2h,Th,Qh,7s,Ac,3c,Ad,7c,9s,6h');
        $west = Hand::createFromString('9h,Ts,5c,Jd,9c,As,8h,Ah,Kh,8d,4c,Qd,5d');
        $table = new Table($north, $east, $south, $west);
        $game = new StubGame($table, Side::North);
        new Turn($game, 1, Card::fromRankSuit('6s'));
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot play suit ♦, because opening suit is ♠.');
        new Turn($game, 2, Card::fromRankSuit('9d'));
    }

    public function testCannotPlayAbsentCardWhileHoldingLedSuit(): void
    {
        $game = new StubGame(self::getFixedTable(), Side::North);
        new Turn($game, 1, Card::fromRankSuit('6s'));
        $eastHand = (string) $game->getCurrentHand();
        try {
            new Turn($game, 2, Card::fromRankSuit('3d'));
            self::fail('Absent card must be rejected.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringStartsWith('Card 3d not present in hand', $exception->getMessage());
        }
        self::assertCount(1, $game->getTurns());
        self::assertEquals(Side::East, $game->getCurrentSide());
        self::assertEquals($eastHand, (string) $game->getCurrentHand());
        new Turn($game, 2, Card::fromRankSuit('4s'));
        self::assertCount(2, $game->getTurns());
    }

    public function testCannotOpenWithAbsentCard(): void
    {
        $game = new StubGame(self::getFixedTable(), Side::North);
        try {
            new Turn($game, 1, Card::fromRankSuit('9d'));
            self::fail('Absent card must be rejected.');
        } catch (\InvalidArgumentException) {
        }
        self::assertCount(0, $game->getTurns());
        self::assertEquals(Side::North, $game->getCurrentSide());
        self::assertCount(13, $game->getCurrentHand());
    }

    public function testRevokeInSecondTrickKeepsSide(): void
    {
        $game = new StubGame(self::getFixedTable(), Side::North);
        new Turn($game, 1, Card::fromRankSuit('6s'));
        new Turn($game, 2, Card::fromRankSuit('4s'));
        new Turn($game, 3, Card::fromRankSuit('7s'));
        new Turn($game, 4, Card::fromRankSuit('As'));
        self::assertEquals(Side::West, $game->getCurrentSide());
        new Turn($game, 5, Card::fromRankSuit('Ts'));
        try {
            new Turn($game, 6, Card::fromRankSuit('4h'));
            self::fail('Revoke must be rejected.');
        } catch (\DomainException $exception) {
            self::assertEquals('Cannot play suit ♥, because opening suit is ♠.', $exception->getMessage());
        }
        self::assertCount(5, $game->getTurns());
        self::assertEquals(Side::North, $game->getCurrentSide());
    }

    public function testDiscardWhenVoidInLedSuit(): void
    {
        $game = new StubGame(self::getFixedTable(), Side::North);
        // trick 1, North leads: West wins
        new Turn($game, 1, Card::fromRankSuit('6s'));
        new Turn($game, 2, Card::fromRankSuit('4s'));
        new Turn($game, 3, Card::fromRankSuit('7s'));
        new Turn($game, 4, Card::fromRankSuit('As'));
        // trick 2, West leads: East wins, North and West are now void in spades
        new Turn($game, 5, Card::fromRankSuit('Ts'));
        new Turn($game, 6, Card::fromRankSuit('3s'));
        new Turn($game, 7, Card::fromRankSuit('Ks'));
        new Turn($game, 8, Card::fromRankSuit('Js'));
        self::assertEquals(Side::East, $game->getCurrentSide());
        // trick 3, East leads: West and North discard clubs
        new Turn($game, 9, Card::fromRankSuit('Qs'));
        new Turn($game, 10, Card::fromRankSuit('9s'));
        new Turn($game, 11, Card::fromRankSuit('5c'));
        new Turn($game, 12, Card::fromRankSuit('2c'));
        self::assertCount(12, $game->getTurns());
        self::assertEquals(3, $game->getWins()->getEastWest());
        self::assertEquals(0, $game->getWins()->getNorthSouth());
        self::assertEquals(Side::East, $game->getCurrentSide());
        // trick 4, East leads hearts: follow-suit check uses the current led suit, not spades
        new Turn($game, 13, Card::fromRankSuit('5h'));
        new Turn($game, 14, Card::fromRankSuit('7h'));
        new Turn($game, 15, Card::fromRankSuit('9h'));
        try {
            new Turn($game, 16, Card::fromRankSuit('Td'));
            self::fail('Revoke must be rejected.');
        } catch (\DomainException $exception) {
            self::assertEquals('Cannot play suit ♦, because opening suit is ♥.', $exception->getMessage());
        }
        new Turn($game, 16, Card::fromRankSuit('4h'));
        self::assertCount(16, $game->getTurns());
        self::assertEquals(4, $game->getWins()->getEastWest());
        self::assertEquals(Side::West, $game->getCurrentSide());
    }

    public function testJoin(): void
    {
        $table = self::getTable();
        $game = new StubGame($table, Side::North);
        self::assertCount(4, $game->getFreeSides());
        $player1 = new StubPlayer('John Doe');
        $game->join($player1, Side::East);
        self::assertCount(3, $game->getFreeSides());
        /** @var Player $playerE */
        $playerE = $game->getPlayerE();
        self::assertTrue($playerE->isEqual($player1));
        self::assertFalse($game->isComplete());
    }

    public function testCannotJoinSameSide(): void
    {
        $table = self::getTable();
        $game = new StubGame($table, Side::North);
        self::assertCount(4, $game->getFreeSides());
        $game->join(new StubPlayer('John Doe'), Side::East);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Side is already taken.');
        $game->join(new StubPlayer('Mike Foster'), Side::East);
    }

    public function testGetDummySide(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        self::assertNull($game->getDummySide());
        // TODO test case when dummy is actually assigned
    }

    public function testGetPlayingPlayer(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $player1 = new StubPlayer('John Doe');
        $game->join($player1, Side::North);
        self::assertTrue($game->getPlayingPlayer()->isEqual($player1));
        self::assertTrue($game->isInTurn($player1));
    }

    public function testGameOver(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        self::assertFalse($game->isGameOver());
    }

    public function testAllPlayers(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $player1 = new StubPlayer('Han Solo');
        $player2 = new StubPlayer('Chew');
        $player3 = new StubPlayer('R2-D2');
        $player4 = new StubPlayer('C-3P0');
        $game->join($player1, Side::North);
        $game->join($player2, Side::East);
        $game->join($player3, Side::South);
        $game->join($player4, Side::West);
        self::assertTrue($game->hasPlayer($player1));
        self::assertTrue($game->hasPlayer($player2));
        self::assertTrue($game->hasPlayer($player3));
        self::assertTrue($game->hasPlayer($player4));
    }

    public function testisDummy(): void
    {
        $game = new StubGame(self::getTable(), Side::North);
        $player = new StubPlayer('Leia Organa');
        $game->join($player, Side::North);
        self::assertFalse($game->isDummy($player));
        self::assertFalse($game->isDummySide(Side::North));
    }

    private static function getFixedTable(): Table
    {
        return new Table(
            Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d'),
            Hand::createFromString('9d,Jh,5s,8c,Ks,4s,5h,4d,8s,Jc,2d,2s,Qs'),
            Hand::createFromString('7h,Kd,Js,2h,Th,Qh,7s,Ac,3c,Ad,7c,9s,6h'),
            Hand::createFromString('9h,Ts,5c,Jd,9c,As,8h,Ah,Kh,8d,4c,Qd,5d'),
        );
    }
}
