<?php

namespace Garak\Bridge;

enum Side: string
{
    case North = 'N';
    case East = 'E';
    case South = 'S';
    case West = 'W';

    public function getName(): string
    {
        return $this->name;
    }

    public function getNext(): self
    {
        return match ($this) {
            self::North => self::East,
            self::East => self::South,
            self::South => self::West,
            self::West => self::North,
        };
    }

    public function getOpposing(): self
    {
        return match ($this) {
            self::North => self::South,
            self::East => self::West,
            self::South => self::North,
            self::West => self::East,
        };
    }

    public function is(string $side): bool
    {
        return $this->value === $side;
    }
}
