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

    public function getOffsets()
    {
        return $this->offsets;
    }

    public function play($board, $player, $piece, $to)
    {
        $stateHandler = $this->backendHandler->getStateHandler();

        $hand = $stateHandler->getHand()[$player];

        if (!$hand[$piece]) {
            $stateHandler->setError("Player does not have tile");
        } elseif (isset($board[$to])) {
            $stateHandler->setError('BoardHandler position is not empty');
        } elseif (count($board) && !hasNeighbour($to, $board)) {
            $stateHandler->setError("board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !neighboursAreSameColor($player, $to, $board)) {
            $stateHandler->setError("BoardHandler position has opposing neighbour");
        } elseif (array_sum($hand) <= 8 && $hand['Q']) {
            $stateHandler->setError('Must play queen bee');
        } else {
            $stateHandler->setBoardPiece($to, $piece);
            $stateHandler->decreasePiece($piece);

            $this->backendHandler->addMove($piece, $to);
        }
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
            if (isNeighbour($a, $b)) {
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
            if ($c != $player && isNeighbour($a, $b)) {
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
        if (!hasNeighbour($to, $board)) {
            return false;
        }
        if (!isNeighbour($from, $to)) {
            return false;
        }
        $b = explode(',', $to);
        $common = [];
        foreach ($this->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if (isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }
        if (!$board[$common[0]] && !$board[$common[1]] && !$board[$from] && !$board[$to]) {
            return false;
        }

        return min(len($board[$common[0]]), len($board[$common[1]]))
            <= max(len($board[$from]), len($board[$to]));
    }
}