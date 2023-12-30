<?php

namespace Board;

use Backend\BackendHandler;

class BoardHandler
{
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

    public function __construct($backendHandler)
    {
        $this->backendHandler = $backendHandler;
    }

    public function getOffsets(): array
    {
        return $this->offsets;
    }

    // Get possible move/play positions
    public function getPossiblePositions($board): array
    {
        $to = [];
        foreach ($this->getOffsets() as $pq) {
            foreach (array_keys($board) as $pos) {
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

    public function play($board, $player, $piece, $to)
    {
        $stateHandler = $this->backendHandler->getStateHandler();

        $hand = $stateHandler->getHand()[$player];

        if (!$hand[$piece]) {
            $stateHandler->setError("Player does not have tile");
        } elseif (isset($board[$to])) {
            $stateHandler->setError('Board position is not empty');
        } elseif (count($board) && !$this->hasNeighbour($to, $board)) {
            $stateHandler->setError("Board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !$this->neighboursAreSameColor($player, $to, $board)) {
            $stateHandler->setError("Board position has opposing neighbour");
        } elseif (array_sum($hand) <= 8 && $hand['Q']) {
            $stateHandler->setError('Must play queen bee');
        } else {
            $stateHandler->setBoardPiece($player, $piece, $to);

            $this->backendHandler->addMove($piece, $to);
        }
    }

    public function move($board, $player, $from, $to)
    {
        $stateHandler = $this->backendHandler->getStateHandler();

        $hand = $stateHandler->getHand()[$player];
        $stateHandler->setError(null);

        if (!isset($board[$from])) {
            $stateHandler->setError("Board position is empty");
        } elseif ($from == $to) {
            $stateHandler->setError("Tile must move");
        } elseif ($board[$from][count($board[$from]) - 1][0] != $player) {
            $stateHandler->setError("Tile is not owned by player");
        } elseif ($hand['Q']) {
            $stateHandler->setError("Queen bee is not played");
        } elseif (!$this->hasNeighbour($to, $board)) {
                $stateHandler->setError("Move would split hive");
        } else {
            // Tile variable can only set if $board[$from] is set
            $tile = array_pop($board[$from]);
            $all = $this->getSetBoardPositionsInOffsets($board);

            if ($all) {
                $stateHandler->setError("Move would split hive");
            } elseif (isset($board[$to]) && $tile[1] != "B") {
                $stateHandler->setError("Tile not empty");
            } elseif (
                ($tile[1] == "Q" || $tile[1] == "B") &&
                !$this->slide($board, $from, $to)
            ) {
                $stateHandler->setError("Tile must slide");
            }

            // Make move if there are no errors
            if ($stateHandler->getError() != null) {
                $board[$to] = [$tile];
                $stateHandler->setBoard($board);

                $this->backendHandler->addMove($from, $to);
            }
        }
    }

    // Make a queue of all set board positions that are in offsets
    private function getSetBoardPositionsInOffsets($board): array
    {
        $all = array_keys($board);
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

        if ($a[0] == $b[0] && abs($a[1] - $b[1]) == 1) {
            return true;
        } elseif ($a[1] == $b[1] && abs($a[0] - $b[0]) == 1) {
            return true;
        } elseif ($a[0] + $a[1] == $b[0] + $b[1]) {
            return true;
        }

        return false;
    }

    private function hasNeighbour($a, $board): bool
    {
        foreach (array_keys($board) as $b) {
            if ($this->isNeighbour($a, $b)) {
                return true;
            }
        }
        return false;
    }

    private function neighboursAreSameColor($player, $a, $board): bool
    {
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

    private function slide($board, $from, $to): bool
    {
        if (!$this->hasNeighbour($to, $board)) {
            return false;
        }
        if (!$this->isNeighbour($from, $to)) {
            return false;
        }
        $b = explode(',', $to);
        $common = [];
        foreach ($this->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if ($this->isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }
        if (!$board[$common[0]] && !$board[$common[1]] && !$board[$from] && !$board[$to]) {
            return false;
        }

        return min($this->len($board[$common[0]]), $this->len($board[$common[1]]))
            <= max($this->len($board[$from]), $this->len($board[$to]));
    }
}
