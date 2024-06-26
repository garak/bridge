<?php

namespace Garak\Bridge;

use Garak\Card\Hand as BaseHand;
use Garak\Card\Suit;

final class Hand extends BaseHand
{
    public function __construct(array $cards, bool $start = true, ?callable $checking = null, ?callable $sorting = null)
    {
        $checking ??= static fn (array $cards): bool => 13 === \count($cards);
        if ($start && false === $checking($cards)) {
            throw new \InvalidArgumentException('Starting hand must be composed of 13 cards.');
        }
        $sorting ??= function (?Suit $trump): void {
            $this->bridgeSort($trump);
        };
        $this->cards = $cards;
        $this->sorting = $sorting;
    }

    public function bridgeSort(?Suit $trump): void
    {
        CardSorter::sort($this->cards, $trump);
        $this->sorted = true;
        $this->sortedSuit = $trump;
    }
}
