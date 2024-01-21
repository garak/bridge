<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Hand;
use Garak\Bridge\Table;
use Garak\Card\Card;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    public function testDuplicatedCard(): void
    {
        $north = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $east = Hand::createFromString('6s,Jh,5s,8c,Ks,4s,5h,4d,8s,Jc,2d,2s,Qs');
        $south = Hand::createFromString('7h,Kd,Js,2h,Th,Qh,7s,Ac,3c,Ad,7c,9s,6h');
        $west = Hand::createFromString('9h,Ts,5c,Jd,9c,As,8h,Ah,Kh,8d,4c,Qd,5d');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot assign same cards: 6♠');
        new Table($north, $east, $south, $west);
    }

    public function testGetSortedSides(): void
    {
        $deck = Card::getDeck(true);
        $cards = \array_chunk($deck, 13);

        $north = new Hand($cards[0], true);
        $east = new Hand($cards[1], true);
        $south = new Hand($cards[2], true);
        $west = new Hand($cards[3], true);

        $table = new Table($north, $east, $south, $west);
        $this->assertCount(13, $table->getNorth(true)->getCards());
        $this->assertCount(13, $table->getEast(true)->getCards());
        $this->assertCount(13, $table->getSouth(true)->getCards());
        $this->assertCount(13, $table->getWest(true)->getCards());
    }
}
