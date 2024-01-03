<?php

namespace Board;

use Backend\BackendHandler;
use State\StateHandler;

class BoardHandler
{
    // All possible directions to move in relative to a position
    private array $offsets =
        [
            [0, 1],
            [0, -1],
            [1, 0],
            [-1, 0],
            [-1, 1],
            [1, -1]
        ];
    private BackendHandler $backendHandler;
    private StateHandler $stateHandler;

    public function __construct($backendHandler)
    {
        $this->backendHandler = $backendHandler;
        $this->stateHandler = $this->backendHandler->getStateHandler();
    }

    public function getOffsets(): array
    {
        return $this->offsets;
    }

    // Get possible move/play positions
    public function getPossiblePositions(): array
    {
        $to = [];
        foreach ($this->getOffsets() as $pq) {
            foreach (array_keys($this->stateHandler->getBoard()) as $pos) {
                $pq2 = explode(',', $pos);
                $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
            }
        }
        $to = array_unique($to);
        if (!count($to)) {
            $to[] = '0,0';
        }

        return $to;
    }

    public function getAvailableHandPieces(): array
    {
        $player = $this->stateHandler->getPlayer();
        $hand = $this->stateHandler->getHand()[$player];

        $pieces = [];
        foreach ($hand as $tile => $ct) {
            if ($ct > 0) {
                $pieces[] = $tile;
            }
        }

        return $pieces;
    }

    public function getPlayerPiecePositions(): array
    {
        $to = [];
        foreach ($this->stateHandler->getBoard() as $pos => $tiles) {
            if (end($tiles)[0] == $this->stateHandler->getPlayer()) {
                $to[] = $pos;
            }
        }

        return $to;
    }

    public function play($piece, $to)
    {
        $player = $this->stateHandler->getPlayer();
        $hand = $this->stateHandler->getHand()[$player];
        $board = $this->stateHandler->getBoard();

        if (!$hand[$piece]) {
            $this->stateHandler->setError("Player does not have tile");
        } elseif (isset($board[$to])) {
            $this->stateHandler->setError('Board position is not empty');
        } elseif (count($board) && !$this->hasNeighbour($to)) {
            $this->stateHandler->setError("Board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !$this->neighboursAreSameColor($to)) {
            $this->stateHandler->setError("Board position has opposing neighbour");
        } elseif ($piece != 'Q' && array_sum($hand) <= 8 && $hand['Q']) {
            $this->stateHandler->setError('Must play queen bee');
        } else {
            $this->stateHandler->setError(null);

            $this->stateHandler->setBoardPiece($player, $piece, $to);

            $this->backendHandler->addMove($piece, $to);
        }
    }

    public function move($from, $to)
    {
        $player = $this->stateHandler->getPlayer();
        $hand = $this->stateHandler->getHand()[$player];
        $board = $this->stateHandler->getBoard();

        $this->stateHandler->setError(null);

        if (!isset($board[$from])) {
            $this->stateHandler->setError("Board position is empty");
        } elseif ($from == $to) {
            $this->stateHandler->setError("Tile must move");
        } elseif (
            isset($board[$from][count($board[$from]) - 1]) &&
            $board[$from][count($board[$from]) - 1][0] != $player
        ) {
            $this->stateHandler->setError("Tile is not owned by player");
        } elseif ($hand['Q']) {
            $this->stateHandler->setError("Queen bee is not played");
        } elseif (!$this->hasNeighbour($to)) {
                $this->stateHandler->setError("Move would split hive");
        } else {
            // Tile variable can only set if $board[$from] is set
            $tile = array_pop($board[$from]);
            $all = $this->getSplitTiles();

            if ($all) {
                $this->stateHandler->setError("Move would split hive");
            } elseif (isset($board[$to]) && $tile[1] != "B") {
                $this->stateHandler->setError("Tile not empty");
            } elseif (
                ($tile[1] == "Q" || $tile[1] == "B") &&
                !$this->slide($from, $to)
            ) {
                $this->stateHandler->setError("Tile must slide");
            } else {
                $this->stateHandler->setError(null);

                unset($board[$from]);
                $board[$to] = [$tile];
                $this->stateHandler->setBoard($board);

                $this->backendHandler->addMove($from, $to);
            }
        }
    }

    // Makes an array of all tiles that are not attached to the hive
    private function getSplitTiles(): array
    {
        $all = array_keys($this->stateHandler->getBoard());
        $queue = [array_shift($all)];

        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($this->offsets as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];
                if (in_array("$p,$q", $all)) {
                    $queue[] = "$p,$q";
                    $all = array_diff($all, ["$p,$q"]);
                }
            }
        }

        return $all;
    }

    private function isNeighbour($a, $b): bool
    {
        $a = explode(',', $a);
        $b = explode(',', $b);

        if (
            $a[0] == $b[0] && abs($a[1] - $b[1]) == 1 ||
            $a[1] == $b[1] && abs($a[0] - $b[0]) == 1 ||
            $a[0] + $a[1] == $b[0] + $b[1]
        ) {
            return true;
        }

        return false;
    }

    private function hasNeighbour($a): bool
    {
        foreach (array_keys($this->stateHandler->getBoard()) as $b) {
            if ($this->isNeighbour($a, $b)) {
                return true;
            }
        }
        return false;
    }

    private function neighboursAreSameColor($a): bool
    {
        $player = $this->stateHandler->getPlayer();
        $board = $this->stateHandler->getBoard();

        foreach ($board as $b => $st) {
            if (!$st) {
                continue;
            }
            $c = $st[count($st) - 1][0];
            if ($c != $player && $this->isNeighbour($a, $b)) {
                return false;
            }
        }
        return true;
    }

    private function len($tile): int
    {
        return $tile ? count($tile) : 0;
    }

    // Give from and to, and return if move of one position is allowed
    private function slide($from, $to): bool
    {
        if (!$this->hasNeighbour($to) || !$this->isNeighbour($from, $to)) {
            return false;
        }

        $board = $this->stateHandler->getBoard();
        $b = explode(',', $to);
        $common = [];

        // Make array of all neighbouring positions shared by $from and $to position
        foreach ($this->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if ($this->isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }

        // Return false if positions are invalid
        if (
            !isset($board[$common[0]]) && !$board[$common[0]] &&
            !isset($board[$common[1]]) && !$board[$common[1]] &&
            !isset($board[$from]) && !$board[$from] &&
            !isset($board[$to]) && !$board[$to]
        ) {
            return false;
        }

        $firstCommonLen = $board[$common[0]] ?? 0;
        $firstCommonLen = $this->len($firstCommonLen);

        $secondCommonLen = $board[$common[1]] ?? 0;
        $secondCommonLen = $this->len($secondCommonLen);

        $fromLen = $board[$from] ?? 0;
        $fromLen = $this->len($fromLen);

        $toLen = $board[$to] ?? 0;
        $toLen = $this->len($toLen);

        // TODO: Has something to do with multiple tiles on one spot?
        return min($firstCommonLen, $secondCommonLen)
            <= max($fromLen, $toLen);
    }
}
