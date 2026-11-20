<?php

namespace Garak\Bridge;

use Garak\Card\Suit;

abstract class Auction implements \Stringable
{
    protected Side $side;

    public function __construct(
        protected Game $game,
        protected int $order,
        protected ?int $value,
        protected ?Suit $trump,
    ) {
        $this->side = $game->getCurrentSide();
        $game->advance();
        $game->addAuction($this);
    }

    public function __toString(): string
    {
        return $this->value.(null !== $this->trump ? $this->trump->value : '');
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getSide(): Side
    {
        return $this->side;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function getTrump(): ?Suit
    {
        return $this->trump;
    }

    public function getSuitValue(): int
    {
        return match ($this->trump) {
            null => 16,
            Suit::Clubs => 1,
            Suit::Diamonds => 2,
            Suit::Hearts => 4,
            Suit::Spades => 8,
            default => throw new \LogicException(\sprintf('Invalid trump suit %s.', $this->trump->value)),
        };
    }

    public function isGreaterThan(self $auction): bool
    {
        $auctionTrump = $auction->getTrump();
        if (null === $this->trump && null === $auctionTrump) {
            return $this->value > $auction->getValue();
        }
        if (null !== $this->trump && null !== $auctionTrump && $this->trump === $auctionTrump) {
            // if trump suit is the same, check the value
            return $this->value > $auction->getValue();
        }

        return $this->getSuitValue() > $auction->getSuitValue();
    }

    public function isSameSuit(self $auction): bool
    {
        if (null === $this->trump || null === $auction->getTrump()) {
            return false;
        }

        return $this->trump === $auction->getTrump();
    }
}
