<?php

namespace Garak\Bridge;

use Garak\Card\Card;
use Garak\Card\Suit;

/**
 * A Table is composed by Hands assigned to the four Sides.
 */
final class Table
{
    private Hand $north;

    private Hand $south;

    private Hand $west;

    private Hand $east;

    public function __construct(Hand $north, Hand $east, Hand $south, Hand $west, bool $check = true)
    {
        $this->north = $north;
        $this->east = $east;
        $this->south = $south;
        $this->west = $west;
        if ($check) {
            self::check($north, $east);
            self::check($north, $south);
            self::check($north, $west);
            self::check($east, $south);
            self::check($east, $west);
            self::check($south, $west);
        }
    }

    public function getNorth(?bool $sort = false, ?Suit $trump = null): Hand
    {
        if ($sort) {
            $this->north->bridgeSort($trump);
        }

        return $this->north;
    }

    public function getSouth(?bool $sort = false, ?Suit $trump = null): Hand
    {
        if ($sort) {
            $this->south->bridgeSort($trump);
        }

        return $this->south;
    }

    public function getWest(?bool $sort = false, ?Suit $trump = null): Hand
    {
        if ($sort) {
            $this->west->bridgeSort($trump);
        }

        return $this->west;
    }

    public function getEast(?bool $sort = false, ?Suit $trump = null): Hand
    {
        if ($sort) {
            $this->east->bridgeSort($trump);
        }

        return $this->east;
    }

    public function isEmpty(): bool
    {
        return $this->north->isEmpty() && $this->south->isEmpty() && $this->west->isEmpty() && $this->south->isEmpty();
    }

    /**
     * Check that the same Cards are not assigned to more than a single Side.
     */
    private static function check(Hand $trick1, Hand $trick2): void
    {
        $callback = static fn (Card $card1, Card $card2): int => \strcasecmp($card1, $card2);
        $intersect = \array_uintersect($trick1->getCards(), $trick2->getCards(), $callback);
        if (\count($intersect) > 0) {
            $cards = \array_map(static fn (string $rankSuit): string => Card::fromRankSuit($rankSuit)->toText(), $intersect);
            $error = 'Cannot assign same cards: %s';
            throw new \InvalidArgumentException(\sprintf($error, \implode(', ', $cards)));
        }
    }
}
