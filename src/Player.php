<?php

namespace Garak\Bridge;

abstract class Player implements \Stringable
{
    public function __construct(protected string $name)
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isPlaying(Game $game): bool
    {
        return $game->hasPlayer($this);
    }

    public function isSide(Game $game, Side $side): bool
    {
        return $game->hasPlayer($this, $side);
    }

    abstract public function isEqual(self $player): bool;
}
