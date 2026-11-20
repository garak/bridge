<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Hand;
use Garak\Bridge\Table;
use Garak\Card\Deck;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static function getTable(): Table
    {
        $pile = (new Deck())->createPile(true);
        [$north, $east, $south, $west] = \array_map(static fn (): Hand => new Hand($pile->drawMany(13)), \range(1, 4));

        return new Table($north, $east, $south, $west);
    }
}
