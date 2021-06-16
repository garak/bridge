<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Player;

class StubPlayer extends Player
{
    public function isEqual(Player $player): bool
    {
        return $player->getName() === $this->name;
    }
}
