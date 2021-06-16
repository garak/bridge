<?php

namespace Garak\Bridge;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Garak\Card\Suit;

abstract class Game
{
    protected ?Player $playerN = null;

    protected ?Player $playerE = null;

    protected ?Player $playerS = null;

    protected ?Player $playerW = null;

    protected ?Player $dummy = null;

    protected Table $startingTable;

    protected Table $currentTable;

    protected Side $currentSide;

    protected Wins $wins;

    protected ?Auction $auction = null;

    /** @var Collection<int, Auction> */
    protected Collection $auctions;

    /** @var Collection<int, Turn> */
    protected Collection $turns;

    public function __construct(Table $startingTable, Side $startingSide)
    {
        $this->startingTable = $startingTable;
        $this->currentTable = $startingTable;
        $this->currentSide = $startingSide;
        $this->wins = new Wins();
        $this->turns = new ArrayCollection();
        $this->auctions = new ArrayCollection();
    }

    public function join(Player $player, Side $side): void
    {
        if (null !== $this->{'player'.$side}) {
            throw new \DomainException('Side is already taken.');
        }
        $this->{'player'.$side} = $player;
    }

    public function advance(): void
    {
        $this->currentSide = $this->currentSide->getNext();
    }

    public function getCurrentSide(): Side
    {
        return $this->currentSide;
    }

    public function getCurrentHand(): Hand
    {
        return $this->currentTable->{'get'.$this->currentSide->getName()}();
    }

    public function getStartingTable(): Table
    {
        return $this->startingTable;
    }

    public function getCurrentTable(): Table
    {
        return $this->currentTable;
    }

    public function getPlayerN(): ?Player
    {
        return $this->playerN;
    }

    public function getPlayerE(): ?Player
    {
        return $this->playerE;
    }

    public function getPlayerS(): ?Player
    {
        return $this->playerS;
    }

    public function getPlayerW(): ?Player
    {
        return $this->playerW;
    }

    public function hasPlayer(Player $player, ?Side $side = null): bool
    {
        if (null !== $side) {
            return null !== $this->{'player'.$side} && $this->{'player'.$side}->isEqual($player);
        }
        if (null !== $this->playerN && $this->playerN->isEqual($player)) {
            return true;
        }
        if (null !== $this->playerE && $this->playerE->isEqual($player)) {
            return true;
        }
        if (null !== $this->playerS && $this->playerS->isEqual($player)) {
            return true;
        }
        if (null !== $this->playerW && $this->playerW->isEqual($player)) {
            return true;
        }

        return false;
    }

    public function getPlayingPlayer(): Player
    {
        if (null !== $this->dummy && $this->{'player'.$this->currentSide}->isEqual($this->dummy)) {
            return $this->{'player'.$this->currentSide->getOpposing()};
        }

        return $this->{'player'.$this->currentSide};
    }

    public function isInTurn(Player $player): bool
    {
        return $player->isEqual($this->getPlayingPlayer());
    }

    public function isGameOver(): bool
    {
        return $this->currentTable->isEmpty();
    }

    public function getWins(): Wins
    {
        return $this->wins;
    }

    /** @return array<int, Side> */
    public function getFreeSides(): array
    {
        $sides = [];
        foreach (Side::SIDES as $short => $name) {
            if (null === $this->{'player'.$short}) {
                $sides[] = new Side($short);
            }
        }

        return $sides;
    }

    public function getDummySide(): ?Side
    {
        if (null === $this->dummy) {
            return null;
        }
        foreach (Side::SIDES as $short => $name) {
            /** @var Player $player */
            $player = $this->{'player'.$short};
            if (null !== $player && $player->isEqual($this->dummy)) {
                return new Side($short);
            }
        }

        return null;
    }

    public function isDummySide(Side $side): bool
    {
        if (null === $this->dummy || null === $this->{'player'.$side}) {
            return false;
        }

        return $this->dummy->isEqual($this->{'player'.$side});
    }

    public function isDummy(Player $player): bool
    {
        if (null === $this->dummy) {
            return false;
        }

        return $player->isEqual($this->dummy);
    }

    public function isComplete(): bool
    {
        return null !== $this->playerN && null !== $this->playerE && null !== $this->playerS && null !== $this->playerS;
    }

    public function addTurn(Turn $turn): void
    {
        $this->checkTurn($turn);
        $this->turns->add($turn);
        $nordHand = $this->currentTable->getNorth();
        $eastHand = $this->currentTable->getEast();
        $southHand = $this->currentTable->getSouth();
        $westHand = $this->currentTable->getWest();
        $side = $turn->getSide()->getName();
        /** @var Hand $hand */
        $hand = $this->currentTable->{'get'.$side}();
        ${'hand'.$side} = $hand->play($turn->getCard());
        $this->currentTable = new Table($nordHand, $eastHand, $southHand, $westHand);
        $this->updateWins();
    }

    /** @return Collection<int, Turn> */
    public function getTurns(): Collection
    {
        return $this->turns;
    }

    public function addAuction(Auction $auction): void
    {
        if (!$this->isGreaterThanPrevious($auction)) {
            throw new \DomainException('Auction must be greater than previous one.');
        }
        $this->auctions->add($auction);
        // with 3 passes, auction is ended
        if ($this->isAuctionEnded()) {
            $this->auction = $this->getLastValidAuction();
            $side = $this->getAuctionSide();
            $this->currentSide = $side->getNext();
            $dummySide = $side->getOpposing();
            $this->dummy = $this->{'player'.$dummySide};
        }
    }

    /** @return Collection<int, Auction> */
    public function getAuctions(): Collection
    {
        return $this->auctions;
    }

    public function getAuction(): ?Auction
    {
        return $this->auction;
    }

    public function getAuctionTrump(): ?Suit
    {
        return null === $this->auction ? null : $this->auction->getTrump();
    }

    /**
     * @return array<int, Turn>
     *
     * @throws \Exception
     */
    public function getTableTurns(): array
    {
        $count = $this->turns->count();
        $start = (int) \floor($count / 4) * 4;
        $num = ($count % 4);
        if ($count > 0 && 0 === $count % 4) {
            // correction to show all 4 cards when turn ends
            $start = (($start / 4) - 1) * 4;
            $num = 4;
        }

        return \array_slice(\iterator_to_array($this->getOrderedTurns()), $start, $num);
    }

    public function getTrump(): ?Suit
    {
        return null === $this->auction ? null : $this->auction->getTrump();
    }

    public function isFinished(): bool
    {
        return $this->currentTable->isEmpty();
    }

    /**
     * @return \ArrayIterator<int, Turn>
     *
     * @throws \Exception
     */
    private function getOrderedTurns(): \ArrayIterator
    {
        /** @var \ArrayIterator<int, Turn> $iterator */
        $iterator = $this->turns->getIterator();
        $iterator->uasort(static fn (Turn $t1, Turn $t2): int => $t1->getOrder() <=> $t2->getOrder());

        return $iterator;
    }

    private function checkTurn(Turn $turn): void
    {
        $order = $turn->getOrder();
        if (1 === $order % 4) {
            return; // opening doesn't need any check
        }
        $openingSuit = $this->getOpeningSuit($order);
        $playingSuit = $turn->getCard()->getSuit();
        if ($playingSuit->isEqual($openingSuit)) {
            // playing turn has same suit as the opening one. OK!
            return;
        }
        // player had opening suit, but they played another one. This is forbidden!
        $side = $turn->getSide()->getName();
        $currentPlayerCards = $this->currentTable->{'get'.$side}()->getCards();
        foreach ($currentPlayerCards as $card) {
            if ($card->getSuit()->isEqual($openingSuit)) {
                $error = 'Cannot play suit %s, because opening suit is %s.';
                throw new \DomainException(\sprintf($error, $playingSuit->getSymbol(), $openingSuit->getSymbol()));
            }
        }
    }

    private function getOpeningSuit(int $order): Suit
    {
        $rest = $order % 4;
        if (2 === $rest) {
            $opt = $order - 1;
        } elseif (3 === $rest) {
            $opt = $order - 2;
        } else {
            $opt = $order - 3;
        }
        /** @var Turn $previousTurn */
        $previousTurn = $this->turns->filter(static fn (Turn $turn): bool => $turn->getOrder() === $opt)->first();

        return $previousTurn->getCard()->getSuit();
    }

    private function updateWins(): void
    {
        $tableTurns = $this->getTableTurns();
        if (\count($tableTurns) < 4) {
            return;
        }
        // create a trick with cards played in turns
        $cards = [];
        foreach ($tableTurns as $turn) {
            $cards[$turn->getSide()->getSide()] = $turn->getCard();
        }
        $trick = new Trick($cards);
        $winner = $trick->getWinningCard($this->getTrump());
        /** @var string $winnerSide */
        $winnerSide = \array_search($winner, $cards, true);
        $side = Side::SIDES[$winnerSide];
        $this->wins = $this->wins->{$side.'Wins'}();
        $this->currentSide = new Side($winnerSide);
    }

    /**
     * @return \ArrayIterator<int, Auction>
     *
     * @throws \Exception
     */
    private function getOrderedAuctions(): \ArrayIterator
    {
        /** @var \ArrayIterator<int, Auction> $iterator */
        $iterator = $this->auctions->getIterator();
        $iterator->uasort(static fn (Auction $l1, Auction $l2): int => $l2->getOrder() <=> $l1->getOrder());

        return $iterator;
    }

    private function isGreaterThanPrevious(Auction $auction): bool
    {
        if ($this->auctions->count() < 1 || null === $auction->getValue()) {
            return true;    // first auction, or pass
        }
        $last3 = \array_slice(\iterator_to_array($this->getOrderedAuctions()), 0, 3);
        foreach ($last3 as $previous) {
            if (null === $previous->getValue()) {
                continue;
            }

            return $auction->isGreaterThan($previous);
        }

        return true;
    }

    private function isAuctionEnded(): bool
    {
        if ($this->auctions->count() < 4) {
            return false;
        }
        $last3 = \array_slice(\iterator_to_array($this->getOrderedAuctions()), 0, 3);
        foreach ($last3 as $auction) {
            if (null !== $auction->getValue()) {
                return false;
            }
        }

        return true;
    }

    private function getLastValidAuction(): Auction
    {
        return \array_slice(\iterator_to_array($this->getOrderedAuctions()), 3, 1)[0];
    }

    private function getAuctionSide(): Side
    {
        // with less than 2 full turns, last auction wins
        if ($this->auctions->count() < 7) {
            return $this->getLastValidAuction()->getSide();
        }
        // if winner's mate proposed trump, side is his/her one
        $last7 = \array_slice(\iterator_to_array($this->getOrderedAuctions()), 0, 7);
        /** @var Auction $mateLast */
        $mateLast = $last7[6];
        /** @var Auction $last */
        $last = $last7[3];
        $lastValidAuction = $this->getLastValidAuction();
        if ($mateLast->isSameSuit($last)) {
            return $lastValidAuction->getSide()->getOpposing();
        }

        return $lastValidAuction->getSide();
    }
}
