<?php

namespace Garak\Bridge;

use Garak\Card\Card;
use Garak\Card\Suit;

final class CardSorter
{
    /** @param array<int|string, Card> $cards */
    public static function sort(array &$cards, ?Suit $trump): void
    {
        \usort($cards, static function (Card $card1, Card $card2) use ($trump): int {
            if (null !== $trump && ($card1->getSuit()->isEqual($trump) || $card2->getSuit()->isEqual($trump))) {
                if ($card1->getSuit()->isEqual($trump) && !$card2->getSuit()->isEqual($trump)) {
                    return 1;
                }
                if (!$card1->getSuit()->isEqual($trump) && $card2->getSuit()->isEqual($trump)) {
                    return -1;
                }

                return $card1->getRank()->getInt() <=> $card2->getRank()->getInt();
            }

            if ($card1->getSuit()->isEqual($card2->getSuit())) {
                return $card1->getRank()->getInt() <=> $card2->getRank()->getInt();
            }

            return $card1->getSuit()->getInt() <=> $card2->getSuit()->getInt();
        });
        $cards = \array_reverse($cards);
    }
}
