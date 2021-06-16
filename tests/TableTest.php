<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Hand;
use Garak\Bridge\Table;
use PHPUnit\Framework\TestCase;

final class TableTest extends TestCase
{
    public function testDuplicatedCard(): void
    {
        /** @var Hand $north */
        $north = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        /** @var Hand $east */
        $east = Hand::createFromString('6s,Jh,5s,8c,Ks,4s,5h,4d,8s,Jc,2d,2s,Qs');
        /** @var Hand $south */
        $south = Hand::createFromString('7h,Kd,Js,2h,Th,Qh,7s,Ac,3c,Ad,7c,9s,6h');
        /** @var Hand $west */
        $west = Hand::createFromString('9h,Ts,5c,Jd,9c,As,8h,Ah,Kh,8d,4c,Qd,5d');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot assign same cards: 6♠');
        new Table($north, $east, $south, $west);
    }
}
