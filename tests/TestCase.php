<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Hand;
use Garak\Bridge\Table;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected static function getTable(): Table
    {
        /** @var Hand $north */
        /** @var Hand $east */
        /** @var Hand $south */
        /** @var Hand $west */
        [$north, $east, $south, $west] = Hand::deal();

        return new Table($north, $east, $south, $west);
    }
}
