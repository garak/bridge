<?php

namespace Garak\Bridge;

/**
 * Represent the number of wins of the two couples of players.
 */
final class Wins
{
    private int $northSouth;

    private int $eastWest;

    public function __construct(int $northSouth = 0, int $eastWest = 0)
    {
        $this->northSouth = $northSouth;
        $this->eastWest = $eastWest;
    }

    public function northWins(): self
    {
        return $this->southWins();
    }

    public function southWins(): self
    {
        return new self($this->northSouth + 1, $this->eastWest);
    }

    public function westWins(): self
    {
        return $this->eastWins();
    }

    public function eastWins(): self
    {
        return new self($this->northSouth, $this->eastWest + 1);
    }

    public function getNorthSouth(): int
    {
        return $this->northSouth;
    }

    public function getEastWest(): int
    {
        return $this->eastWest;
    }
}
