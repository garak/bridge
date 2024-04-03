# PHP Bridge library

[![Latest Stable Version](http://poser.pugx.org/garak/bridge/v)](https://packagist.org/packages/garak/bridge)
[![Latest Unstable Version](http://poser.pugx.org/garak/bridge/v/unstable)](https://packagist.org/packages/garak/bridge) 
[![License](http://poser.pugx.org/garak/bridge/license)](https://packagist.org/packages/garak/bridge) 
[![PHP Version Require](http://poser.pugx.org/garak/bridge/require/php)](https://packagist.org/packages/garak/bridge)
[![Maintainability](https://api.codeclimate.com/v1/badges/2241c6ad53dd85ccd18b/maintainability)](https://codeclimate.com/github/garak/bridge/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/2241c6ad53dd85ccd18b/test_coverage)](https://codeclimate.com/github/garak/bridge/test_coverage)

![https://commons.wikimedia.org/wiki/File:Four_overlapping_playing_cards.jpg](https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Four_overlapping_playing_cards.jpg/120px-Four_overlapping_playing_cards.jpg)

## Introduction

This library offers some PHP classes useful for creating a Bridge card game:

* Game _(to be extended)_
* Player _(to be extended)_
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
$game  = new Game($table, new Side('N'));   // second argument is starting side
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

## TODO

Auction implementation is still to be done...
