<?php

namespace Garak\Bridge;

use Garak\Card\Card;
use Garak\Card\CardsTrick;
use Garak\Card\Suit;

final class Trick extends CardsTrick
{
    /**
     * The winning card of a trick depends on the opening suit.
     * If the trump suit is not played, the winning card is the higher one matching the opening suit.
     */
    public function getWinningCard(?Suit $trump): Card
    {
        if (false === $firstCard = \reset($this->cards)) {
            throw new \DomainException('No cards in trick.');
        }
        $firstSuit = $firstCard->getSuit();
        if (null !== $trump) {
            // case 1: opening with the trump suit, sorting is enough
            if ($trump->isEqual($firstSuit)) {
                CardSorter::sort($this->cards, null);
                \reset($this->cards);

                return $this->cards[0];
            }
            // case 2: one of the played suits is the trump suit, sorting is enough as well
            foreach ($this->cards as $card) {
                if ($card->getSuit()->isEqual($trump)) {
                    CardSorter::sort($this->cards, $trump);
                    \reset($this->cards);

                    return $this->cards[0];
                }
            }
        }
        // case 3: we treat first played suit as if it were the trump
        CardSorter::sort($this->cards, $trump);
        \reset($this->cards);

        return $this->cards[0];
    }
}
