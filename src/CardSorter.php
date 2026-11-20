<?php

namespace Garak\Bridge;

use Garak\Card\Card;
use Garak\Card\CardComparator;
use Garak\Card\Suit;

final class CardSorter
{
    /**
     * Highest first, trump suit (if any) on top, then spades, hearts, diamonds, clubs.
     *
     * @param array<int|string, Card> $cards
     */
    public static function sort(array &$cards, ?Suit $trump): void
    {
        $cards = (new CardComparator(trump: $trump, descending: true))->sort($cards);
    }
}
