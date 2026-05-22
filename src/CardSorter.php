<?php

namespace Garak\Bridge;

use Garak\Card\Card;
use Garak\Card\Suit;

final class CardSorter
{
    /** @param array<int|string, Card> $cards */
    public static function sort(array &$cards, ?Suit $trump): void
    {
        \usort($cards, static function (Card $card2, Card $card1) use ($trump): int {
            $suit1 = $card1->getSuit();
            $suit2 = $card2->getSuit();
            $rank1 = $card1->getRank()->getInt();
            $rank2 = $card2->getRank()->getInt();

            if (null !== $trump && ($suit1 === $trump || $suit2 === $trump)) {
                // trump cards sort first; when both are trump, higher rank wins
                $suitOrder = ($suit1 === $trump) <=> ($suit2 === $trump);

                return 0 !== $suitOrder ? $suitOrder : $rank1 <=> $rank2;
            }

            return $suit1 !== $suit2 ? $suit1->getInt() <=> $suit2->getInt() : $rank1 <=> $rank2;
        });
    }
}
