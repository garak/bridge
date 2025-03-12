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
        return $this->value.$this->trump;
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
        return null !== $this->trump ? $this->trump->getInt() : 16;
    }

    public function isGreaterThan(self $auction): bool
    {
        $auctionTrump = $auction->getTrump();
        if (null !== $this->trump && null !== $auctionTrump && $this->trump->isEqual($auctionTrump)) {
            // if trump suit is the same, check the value
            return $this->value > $auction->getValue();
        }

        return $this->getSuitValue() > $auction->getSuitValue();
    }

    public function isSameSuit(self $auction): bool
    {
        if (null === $this->trump && null === $auction->getTrump()) {
            return false;
        }
        if (null === $this->trump || null === $auction->getTrump()) {
            return false;
        }

        return $this->trump->isEqual($auction->getTrump());
    }
}
