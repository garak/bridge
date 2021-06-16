<?php

namespace Garak\Bridge;

use Garak\Card\Card;

/**
 * A Turn represent a Card played by a Side in a Game.
 * Turns are sorted by $order.
 */
class Turn
{
    protected Game $game;

    protected int $order;

    protected Side $side;

    protected Card $card;

    public function __construct(Game $game, int $order, Card $card)
    {
        $this->game = $game;
        $this->order = $order;
        $this->side = $game->getCurrentSide();
        $this->card = $card;
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
