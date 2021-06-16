<?php

namespace Garak\Bridge\Test;

use Garak\Bridge\Hand;
use Garak\Card\Card;
use Garak\Card\Suit;
use PHPUnit\Framework\TestCase;

final class HandTest extends TestCase
{
    public function testCannotCreateStartingHandWithLessThan13Cards(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Starting hand must be composed of 13 cards.');
        new Hand([], true);
    }

    public function testHandStringRepresentation(): void
    {
        $string = '6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d';
        $hand = Hand::createFromString($string);
        self::assertEquals($string, (string) $hand);
    }

    public function testHandTextRepresentation(): void
    {
        $hand = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        self::assertEquals('6♠ 3♠ 4♥ 3♥ T♦ 7♦ 6♦ 3♦ K♣ Q♣ T♣ 6♣ 2♣', $hand->toText());
    }

    public function testHandHtmlRepresentation(): void
    {
        $hand = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        self::assertStringStartsWith('<span id="6s" class="crd crd-6 st-s">6♠</span>', $hand->toHtml());
    }

    public function testValidHands(): void
    {
        self::assertTrue(Hand::isValid('6s'));
        self::assertTrue(Hand::isValid('6s,4h,3s'));
        self::assertFalse(Hand::isValid('6'));
    }

    public function testCannotPlayCardNotPresentInHand(): void
    {
        $hand = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Card 5d not present in hand (6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d).');
        $hand->play(Card::fromRankSuit('5d'));
    }

    public function testCannotGetCardFromEmptyHand(): void
    {
        $hand = Hand::createFromString('6s', false);
        $hand = $hand->play(Card::fromRankSuit('6s'));
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No cards left.');
        $hand->getRandomCard();
    }

    public function testSorting(): void
    {
        $hand = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
        $hand->sort(new Suit('d'));
        self::assertEquals('Td,7d,6d,3d,6s,3s,4h,3h,Kc,Qc,Tc,6c,2c', (string) $hand);
        $hand->sort(new Suit('d'));
        $hand->sort(new Suit('h'));
        self::assertEquals('4h,3h,6s,3s,Td,7d,6d,3d,Kc,Qc,Tc,6c,2c', (string) $hand);
    }
}
