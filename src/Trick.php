<?php

namespace Garak\Bridge;

use Garak\Card\Card;
use Garak\Card\CardsTrick;
use Garak\Card\Suit;

final class Trick extends CardsTrick
{
    /**
     * The winning card of a trick follows bridge rules:
     * - If at least one trump card was played, the highest trump wins.
     * - Otherwise, the highest card of the opening (led) suit wins; off-suit discards never win.
     *
     * We achieve both by passing the "effective trump" to CardSorter:
     * - trump suit, when at least one trump card is actually present in the trick, or
     * - the opening suit otherwise, so led-suit cards are always prioritised over discards.
     */
    public function getWinningCard(?Suit $trump): Card
    {
        if (false === $firstCard = \reset($this->cards)) {
            throw new \DomainException('No cards in trick.');
        }
        $openingSuit = $firstCard->getSuit();
        $trumpWasPlayed = null !== $trump
            && null !== \array_find($this->cards, static fn (Card $c): bool => $c->getSuit() === $trump);
        $effectiveTrump = $trumpWasPlayed ? $trump : $openingSuit;
        CardSorter::sort($this->cards, $effectiveTrump);
        \reset($this->cards);

        return $this->cards[0];
    }
}
