<?php

namespace Garak\Bridge;

use Garak\Card\Card;

/**
 * A Turn represents a Card played by a Side in a Game.
 * Turns are sorted by $order.
 */
class Turn
{
    protected Side $side;

    public function __construct(protected Game $game, protected int $order, protected Card $card)
    {
        $this->side = $game->getCurrentSide();
        $game->advance();
        $game->addTurn($this);
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getSide(): Side
    {
        return $this->side;
    }

    public function getCard(): Card
    {
        return $this->card;
    }
}
