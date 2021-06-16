<?php

namespace Garak\Bridge;

abstract class Player
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
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
