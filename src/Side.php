<?php

namespace Garak\Bridge;

final class Side implements \Stringable
{
    public const SIDES = ['N' => 'North', 'E' => 'East', 'S' => 'South', 'W' => 'West'];

    public function __construct(private readonly string $side)
    {
        if (!isset(self::SIDES[$side])) {
            throw new \InvalidArgumentException(\sprintf('Invalid side: %s.', $side));
        }
    }

    public function __toString(): string
    {
        return $this->side;
    }

    public function getSide(): string
    {
        return $this->side;
    }

    public function getName(): string
    {
        return self::SIDES[$this->side];
    }

    public function getNext(): self
    {
        $next = [
            'N' => 'E',
            'E' => 'S',
            'S' => 'W',
            'W' => 'N',
        ];

        return new self($next[$this->side]);
    }

    public function getOpposing(): self
    {
        $oppose = [
            'N' => 'S',
            'E' => 'W',
            'S' => 'N',
            'W' => 'E',
        ];

        return new self($oppose[$this->side]);
    }

    public function is(string $side): bool
    {
        return $this->side === $side;
    }
}
