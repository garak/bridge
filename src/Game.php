<?php

namespace Garak\Bridge;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Garak\Card\Card;
use Garak\Card\Suit;

abstract class Game
{
    protected ?Player $playerN = null;

    protected ?Player $playerE = null;

    protected ?Player $playerS = null;

    protected ?Player $playerW = null;

    protected ?Player $dummy = null;

    protected Table $currentTable;

    protected Side $currentSide;

    protected Wins $wins;

    protected ?Auction $auction = null;

    /** @var Collection<int, Auction> */
    protected Collection $auctions;

    /** @var Collection<int, Turn> */
    protected Collection $turns;

    public function __construct(protected Table $startingTable, Side $startingSide)
    {
        $this->currentTable = $startingTable;
        $this->currentSide = $startingSide;
        $this->wins = new Wins();
        $this->turns = new ArrayCollection();
        $this->auctions = new ArrayCollection();
    }

    public function join(Player $player, Side $side): void
    {
        if (null !== $this->getPlayerForSide($side)) {
            throw new \DomainException('Side is already taken.');
        }
        $this->setPlayerForSide($side, $player);
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
        return match ($this->currentSide) {
            Side::North => $this->currentTable->getNorth(),
            Side::East => $this->currentTable->getEast(),
            Side::South => $this->currentTable->getSouth(),
            Side::West => $this->currentTable->getWest(),
        };
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
            $sidePlayer = $this->getPlayerForSide($side);

            return null !== $sidePlayer && $sidePlayer->isEqual($player);
        }

        return $this->playerN?->isEqual($player)    // @phpstan-ignore-line booleanOr.leftNotBoolean
            || $this->playerE?->isEqual($player)
            || $this->playerS?->isEqual($player)
            || $this->playerW?->isEqual($player)
        ;
    }

    public function getPlayingPlayer(): Player
    {
        $currentPlayer = $this->getPlayerForSide($this->currentSide);
        if (null !== $this->dummy && null !== $currentPlayer && $currentPlayer->isEqual($this->dummy)) {
            return $this->getPlayerForSide($this->currentSide->getOpposing())
                ?? throw new \RuntimeException('No player found for opposing side.');
        }

        return $currentPlayer ?? throw new \RuntimeException('No player found for current side.');
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
        return \array_values(\array_filter(Side::cases(), fn (Side $side): bool => null === $this->getPlayerForSide($side)));
    }

    public function getDummySide(): ?Side
    {
        if (null === $this->dummy) {
            return null;
        }
        $dummy = $this->dummy;

        return \array_find(Side::cases(), fn (Side $side): bool => ($p = $this->getPlayerForSide($side)) !== null && $p->isEqual($dummy));
    }

    public function isDummySide(Side $side): bool
    {
        $player = $this->getPlayerForSide($side);
        if (null === $this->dummy || null === $player) {
            return false;
        }

        return $this->dummy->isEqual($player);
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
        return null !== $this->playerN && null !== $this->playerE && null !== $this->playerS && null !== $this->playerW;
    }

    public function addTurn(Turn $turn): void
    {
        try {
            $this->checkTurn($turn);
        } catch (\DomainException|\InvalidArgumentException $exception) {
            // Turn constructor advances side before validation; rollback on rejection.
            $this->currentSide = $turn->getSide();

            throw $exception;
        }
        $this->turns->add($turn);
        $northHand = $this->currentTable->getNorth();
        $eastHand = $this->currentTable->getEast();
        $southHand = $this->currentTable->getSouth();
        $westHand = $this->currentTable->getWest();
        match ($turn->getSide()) {
            Side::North => $northHand = $northHand->play($turn->getCard()),
            Side::East => $eastHand = $eastHand->play($turn->getCard()),
            Side::South => $southHand = $southHand->play($turn->getCard()),
            Side::West => $westHand = $westHand->play($turn->getCard()),
        };
        $this->currentTable = new Table($northHand, $eastHand, $southHand, $westHand);
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
            // Auction constructor advances side before validation; rollback on rejection.
            $this->currentSide = $auction->getSide();
            throw new \DomainException('Auction must be greater than previous one.');
        }
        $this->auctions->add($auction);
        // with 3 passes, auction is ended
        if ($this->isAuctionEnded()) {
            $this->auction = $this->getLastValidAuction();
            $side = $this->getAuctionSide();
            $this->currentSide = $side->getNext();
            $dummySide = $side->getOpposing();
            $this->dummy = $this->getPlayerForSide($dummySide);
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
        return $this->auction?->getTrump();
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
        $card = $turn->getCard();
        $hand = match ($turn->getSide()) {
            Side::North => $this->currentTable->getNorth(),
            Side::East => $this->currentTable->getEast(),
            Side::South => $this->currentTable->getSouth(),
            Side::West => $this->currentTable->getWest(),
        };
        if (!$hand->has($card)) {
            throw new \InvalidArgumentException(\sprintf('Card %s not present in hand (%s).', $card, $hand));
        }
        if (1 === $turn->getOrder() % 4) {
            return; // opening can be any card in hand
        }
        $trick = new Trick(\array_map(static fn (Turn $t): Card => $t->getCard(), $this->getTableTurns()));
        if (!$trick->canPlay($card, $hand)) {
            $openingSuit = $trick->getLedSuit() ?? throw new \LogicException('No opening card in trick.');
            throw new \DomainException(\sprintf('Cannot play suit %s, because opening suit is %s.', $card->getSuit()->getSymbol(), $openingSuit->getSymbol()));
        }
    }

    /**
     * @throws \Exception
     */
    private function updateWins(): void
    {
        $tableTurns = $this->getTableTurns();
        if (\count($tableTurns) < 4) {
            return;
        }
        // create a trick with cards played in turns
        $cards = [];
        foreach ($tableTurns as $turn) {
            $cards[$turn->getSide()->value] = $turn->getCard();
        }
        $trick = new Trick($cards);
        $winnerSide = Side::from((string) $trick->getWinningKey($this->getTrump()));
        $this->wins = match ($winnerSide) {
            Side::North => $this->wins->northWins(),
            Side::East => $this->wins->eastWins(),
            Side::South => $this->wins->southWins(),
            Side::West => $this->wins->westWins(),
        };
        $this->currentSide = $winnerSide;
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

    /**
     * @throws \Exception
     */
    private function isGreaterThanPrevious(Auction $auction): bool
    {
        if ($this->auctions->count() < 1 || null === $auction->getValue()) {
            return true;    // first auction, or pass
        }
        $last3 = \array_slice(\iterator_to_array($this->getOrderedAuctions()), 0, 3);
        $previous = \array_find($last3, static fn (Auction $a): bool => null !== $a->getValue());

        return null === $previous || $auction->isGreaterThan($previous);
    }

    /**
     * @throws \Exception
     */
    private function isAuctionEnded(): bool
    {
        if ($this->auctions->count() < 4) {
            return false;
        }
        $last3 = \array_slice(\iterator_to_array($this->getOrderedAuctions()), 0, 3);

        return null === \array_find($last3, static fn (Auction $a): bool => null !== $a->getValue());
    }

    /**
     * @throws \Exception
     */
    private function getLastValidAuction(): Auction
    {
        return \array_slice(\iterator_to_array($this->getOrderedAuctions()), 3, 1)[0];
    }

    /**
     * @throws \Exception
     */
    private function getAuctionSide(): Side
    {
        $lastValidAuction = $this->getLastValidAuction();
        $winningSide = $lastValidAuction->getSide();
        $mateSide = $winningSide->getOpposing();

        $auctions = \iterator_to_array($this->auctions);
        \usort($auctions, static fn (Auction $a, Auction $b): int => $a->getOrder() <=> $b->getOrder());

        $found = \array_find($auctions, static function (Auction $auction) use ($lastValidAuction, $winningSide, $mateSide): bool {
            if (null === $auction->getValue()) {
                return false;
            }
            $isNoTrump = null === $lastValidAuction->getTrump() && null === $auction->getTrump();
            $isSameSuit = null !== $lastValidAuction->getTrump() && $auction->isSameSuit($lastValidAuction);

            return ($isNoTrump || $isSameSuit) && ($auction->getSide() === $winningSide || $auction->getSide() === $mateSide);
        });

        return $found?->getSide() ?? $winningSide;
    }

    private function getPlayerForSide(Side $side): ?Player
    {
        return match ($side) {
            Side::North => $this->playerN,
            Side::East => $this->playerE,
            Side::South => $this->playerS,
            Side::West => $this->playerW,
        };
    }

    private function setPlayerForSide(Side $side, Player $player): void
    {
        match ($side) {
            Side::North => $this->playerN = $player,
            Side::East => $this->playerE = $player,
            Side::South => $this->playerS = $player,
            Side::West => $this->playerW = $player,
        };
    }
}
