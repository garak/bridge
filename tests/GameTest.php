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
}
