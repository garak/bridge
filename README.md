# PHP Bridge library

[![Latest Stable Version](http://poser.pugx.org/garak/bridge/v)](https://packagist.org/packages/garak/bridge)
[![Latest Unstable Version](http://poser.pugx.org/garak/bridge/v/unstable)](https://packagist.org/packages/garak/bridge) 
[![License](http://poser.pugx.org/garak/bridge/license)](https://packagist.org/packages/garak/bridge) 
[![PHP Version Require](http://poser.pugx.org/garak/bridge/require/php)](https://packagist.org/packages/garak/bridge)
[![Maintainability](https://qlty.sh/gh/garak/projects/bridge/maintainability.svg)](https://qlty.sh/gh/garak/projects/bridge)
[![Code Coverage](https://qlty.sh/gh/garak/projects/bridge/coverage.svg)](https://qlty.sh/gh/garak/projects/bridge)

![https://commons.wikimedia.org/wiki/File:Four_overlapping_playing_cards.jpg](https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Four_overlapping_playing_cards.jpg/120px-Four_overlapping_playing_cards.jpg)

## Introduction

This library offers some PHP classes useful for creating a Bridge card game:

* Game _(needs to be extended)_
* Player _(needs to be extended)_
* Table
* Turn
* Hand
* Side
* Wins

## Installation

Run `composer require garak/bridge`.

## Usage

Here is an example of the first turn of a game:

```php
<?php

require 'vendor/autoload.php';

use App\Game;   // this is your Game class, extending \Garak\Bridge\Game
use App\Player;   // this is your Player class, extending \Garak\Bridge\Player
use Garak\Bridge\Hand;
use Garak\Bridge\Side;
use Garak\Bridge\Table;
use Garak\Bridge\Turn;

$north = Hand::createFromString('6s,4h,3s,Td,6c,3d,3h,Kc,Qc,Tc,7d,2c,6d');
$east  = Hand::createFromString('9d,Jh,5s,8c,Ks,4s,5h,4d,8s,Jc,2d,2s,Qs');
$south = Hand::createFromString('7h,Kd,Js,2h,Th,Qh,7s,Ac,3c,Ad,7c,9s,6h');
$west  = Hand::createFromString('9h,Ts,5c,Jd,9c,As,8h,Ah,Kh,8d,4c,Qd,5d');
$table = new Table($north, $east, $south, $west);
$game  = new Game($table, startingSide: new Side('N'));
$game->join(new Player('John Doe'), new Side('N'));
$game->join(new Player('Will Riker'), new Side('E'));
$game->join(new Player('Yoda'), new Side('S'));
$game->join(new Player('Peter Venkman'), new Side('W'));
$game->addTurn(new Turn($game, 1, Card::fromRankSuit('6s')));
$game->addTurn(new Turn($game, 2, Card::fromRankSuit('4s')));
$game->addTurn(new Turn($game, 3, Card::fromRankSuit('7s')));
$game->addTurn(new Turn($game, 4, Card::fromRankSuit('Ts')));
echo $game->getWins()->getEastWest();   // will output "1", since West won the first turn
```

## Auction

The library exposes an abstract `Garak\Bridge\Auction` class that represents one auction entry (a bid or a pass).

Key points

- The `Auction` constructor takes the `Game`, the auction `order`, an optional numeric `value` (bid level), and an optional `Garak\Card\Suit` for trump.
- A `null` value is treated as a pass.
- When an `Auction` is created, it records the bidding side, advances to the next side, and calls `Game::addAuction()`.

Implement a concrete auction class

`Auction` is abstract, so your application must provide a concrete implementation:

```php
<?php

namespace App;

use Garak\Bridge\Auction;

final class Contract extends Auction
{
	public function __toString(): string
	{
		return (string) $this->getValue() . ($this->getTrump() ? (string) $this->getTrump() : '');
	}
}
```

Use auctions in a game

```php
<?php

use App\Contract;
use App\Game;      // your concrete class extending Garak\Bridge\Game
use App\Player;    // your concrete class extending Garak\Bridge\Player
use Garak\Bridge\Side;
use Garak\Card\Suit;

$table = /* create Table as in the Usage example above */;
$game = new Game($table, startingSide: new Side('N'));

$game->join(new Player('North'), new Side('N'));
$game->join(new Player('East'), new Side('E'));
$game->join(new Player('South'), new Side('S'));
$game->join(new Player('West'), new Side('W'));

new Contract($game, 1, 1, new Suit('d')); // 1d from North
new Contract($game, 2, null, null);       // pass from East
new Contract($game, 3, null, null);       // pass from South
new Contract($game, 4, null, null);       // pass from West (auction ends)

$contract = $game->getAuction(); // last valid auction, or null
$trump = $game->getTrump();      // Suit|null
$dummy = $game->getDummySide();  // Side|null

if (null !== $contract) {
	echo (string) $contract; // e.g. "1d"
}
```

Auction ordering rules (summary)

- Same trump suit: higher `value` wins.
- Different suits: suit ranking is used (club < diamond < heart < spade < no-trump).
- `Game::addAuction()` throws `DomainException` when a bid is not greater than the previous valid one.
- The auction is considered ended after three consecutive passes **after at least one non-pass bid**. If everyone passes from the start, there is no winning contract.

