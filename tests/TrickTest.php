<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Trick;
use Garak\Card\Card;
use Garak\Card\Suit;
use PHPUnit\Framework\TestCase;

final class TrickTest extends TestCase
{
    public function testEmptyTrick(): void
    {
        $trick = new Trick([]);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No cards in trick.');
        $trick->getWinningCard(null);
    }

    public function testGetWinningCardWithOpeningTrump(): void
    {
        $trick = new Trick([Card::fromRankSuit('2h'), Card::fromRankSuit('6d')]);
        $card = $trick->getWinningCard(new Suit('h'));
        self::assertEquals('2h', (string) $card);
    }

    public function testGetWinningCardWithTrump(): void
    {
        $trick = new Trick([Card::fromRankSuit('6h'), Card::fromRankSuit('2d')]);
        $card = $trick->getWinningCard(new Suit('d'));
        self::assertEquals('2d', (string) $card);
    }
}
