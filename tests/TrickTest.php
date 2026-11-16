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
        $card = $trick->getWinningCard(Suit::Hearts);
        self::assertEquals('2h', (string) $card);
    }

    public function testGetWinningCardWithTrump(): void
    {
        $trick = new Trick([Card::fromRankSuit('6h'), Card::fromRankSuit('2d')]);
        $card = $trick->getWinningCard(Suit::Diamonds);
        self::assertEquals('2d', (string) $card);
    }

    /**
     * Regression: trump is led but an off-suit card of a higher-ranking suit (Spades > Diamonds)
     * is discarded. The trump card must still win.
     */
    public function testTrumpLedBeatsHigherSuitDiscard(): void
    {
        // Diamonds trump is led; Spades ranks higher but must not win
        $trick = new Trick([Card::fromRankSuit('2d'), Card::fromRankSuit('6s')]);
        $card = $trick->getWinningCard(Suit::Diamonds);
        self::assertEquals('2d', (string) $card);
    }

    /**
     * Regression: no trump card is played, so the highest card of the led suit wins;
     * an off-suit discard of a higher-ranking suit must not win.
     */
    public function testLedSuitWinsWhenNoTrumpPlayed(): void
    {
        // Trump is Clubs, but none played; Hearts led, Spades discarded (Spades ranks higher)
        $trick = new Trick([Card::fromRankSuit('6h'), Card::fromRankSuit('As')]);
        $card = $trick->getWinningCard(Suit::Clubs);
        self::assertEquals('6h', (string) $card);
    }

    public function testGetWinningKeyKeepsPlayerKeys(): void
    {
        $trick = new Trick(['N' => Card::fromRankSuit('6h'), 'E' => Card::fromRankSuit('Ah'), 'S' => Card::fromRankSuit('2c')]);
        self::assertSame('S', $trick->getWinningKey(Suit::Clubs));
        self::assertSame('E', $trick->getWinningKey(null));
    }
}
