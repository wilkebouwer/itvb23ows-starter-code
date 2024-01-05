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

    public function lostGame($player): bool
    {
        foreach ($this->stateHandler->getBoard() as $pos => $tiles) {
            $topTile = end($tiles);

            if (
                $topTile[0] == $player &&
                $topTile[1] == 'Q' &&
                count($this->getNeighbours($pos)) == 6
            ) {
                return true;
            }
        }
        return false;
    }

    public function pass() {
        $player = $this->stateHandler->getPlayer();
        $hand = $this->stateHandler->getHand()[$player];
        $possiblePositions = $this->getPossiblePositions();

        // Test if no pieces can be placed anywhere
        foreach ($possiblePositions as $pos) {
            foreach ($hand as $pieceType => $amount) {
                if (
                    $amount > 0 &&
                    $this->validPlay($pieceType, $pos)
                ) {
                    $this->stateHandler->setError("Play or move is still possible");
                    return;
                }
            }
        }

        // Test if no existing piece can move anywhere
        foreach ($this->stateHandler->getBoard() as $pos => $tiles) {
            $topTile = end($tiles);

            if ($topTile[0] == $player) {
                foreach ($possiblePositions as $to) {
                    if ($this->validMove($pos, $to)) {
                        $this->stateHandler->setError("Play or move is still possible");
                        return;
                    }
                }
            }
        }

        $this->stateHandler->setError(null);
        $this->backendHandler->addMove(null, null);
    }

    public function play($piece, $to)
    {
        $player = $this->stateHandler->getPlayer();

        if ($this->validPlay($piece, $to)) {
            $this->stateHandler->setError(null);

            $this->stateHandler->setBoardPiece($player, $piece, $to);

            $this->backendHandler->addMove($piece, $to);
        }
    }

    public function move($from, $to)
    {
        $board = $this->stateHandler->getBoard();

        $this->stateHandler->setError(null);

        if ($this->validMove($from, $to)) {
            $tile = array_pop($board[$from]);

            $this->stateHandler->setError(null);

            unset($board[$from]);
            $board[$to] = [$tile];
            $this->stateHandler->setBoard($board);

            $this->backendHandler->addMove($from, $to);
        }
    }

    private function validPlay($piece, $to): bool
    {
        $player = $this->stateHandler->getPlayer();
        $hand = $this->stateHandler->getHand()[$player];
        $board = $this->stateHandler->getBoard();

        if (!$hand[$piece]) {
            $this->stateHandler->setError("Player does not have tile");
        } elseif (isset($board[$to])) {
            $this->stateHandler->setError('Board position is not empty');
        } elseif (count($board) && !$this->hasNeighbour($board, $to)) {
            $this->stateHandler->setError("Board position has no neighbour");
        } elseif (array_sum($hand) < 11 && !$this->neighboursAreSameColor($to)) {
            $this->stateHandler->setError("Board position has opposing neighbour");
        } elseif ($piece != 'Q' && array_sum($hand) <= 8 && $hand['Q']) {
            $this->stateHandler->setError('Must play queen bee');
        } else {
            return true;
        }
        return false;
    }

    private function validMove($from, $to): bool
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
        } else {
            // Remove tile from board array
            $tile = array_pop($board[$from]);
            unset($board[$from]);

            if (
                !$this->hasNeighbour($board, $to) ||
                $this->getSplitTiles($board)
            ) {
                $this->stateHandler->setError("Move would split hive");
            } elseif (isset($board[$to]) && $tile[1] != "B") {
                $this->stateHandler->setError("Tile not empty");
            } elseif (
                ($tile[1] == "Q" || $tile[1] == "B") &&
                !$this->slide($from, $to)
            ) {
                $this->stateHandler->setError("Tile must slide");
            } elseif (
                $tile[1] == "A" &&
                !$this->antSlide($from, $to)
            ) {
                $this->stateHandler->setError("Tile must slide");
            } elseif (
                $tile[1] == "S" &&
                !$this->spiderSlide($from, $to)
            ) {
                $this->stateHandler->setError("Tile must slide");
            } else {
                return true;
            }
        }
        return false;
    }

    // Makes an array of all tiles that are not attached to the hive
    private function getSplitTiles($board): array
    {
        $all = array_keys($board);
        $queue = [array_shift($all)];

        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($this->offsets as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];

                $position = $p . "," . $q;

                if (in_array($position, $all)) {
                    $queue[] = $position;
                    $all = array_diff($all, [$position]);
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

    private function hasNeighbour($board, $a): bool
    {
        $b = explode(',', $a);

        foreach ($this->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];

            $position = $p . "," . $q;

            if (isset($board[$position]) &&
                $this->isNeighbour($a, $position)
            ) {
                return true;
            }
        }
        return false;
    }

    private function getNeighbours($a): array
    {
        $board = $this->stateHandler->getBoard();

        $neighbours = [];
        $b = explode(',', $a);

        $common = [];
        foreach ($this->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];

            $position = $p . "," . $q;

            if (
                isset($board[$position]) &&
                $this->isNeighbour($a, $position)
            ) {
                $neighbours[] = $position;
            }
        }

        return $neighbours;
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
        $board = $this->stateHandler->getBoard();

        if (!$this->hasNeighbour($board, $to) || !$this->isNeighbour($from, $to)) {
            return false;
        }

        $b = explode(',', $to);

        // Make array of all neighbouring positions shared by $from and $to position
        $common = [];
        foreach ($this->offsets as $pq) {
            $p = $b[0] + $pq[0];
            $q = $b[1] + $pq[1];
            if ($this->isNeighbour($from, $p.",".$q)) {
                $common[] = $p.",".$q;
            }
        }

        // Return false if positions are invalid
        if (
            (!isset($board[$common[0]]) || !$board[$common[0]]) &&
            (!isset($board[$common[1]]) || !$board[$common[1]]) &&
            (!isset($board[$from]) || !$board[$from]) &&
            (!isset($board[$to]) || !$board[$to])
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

    private function antSlide($from, $to): bool
    {
        $board = $this->stateHandler->getBoard();

        $visited = [];
        $tiles = array($from);

        // Find if path exists between $from and $to using DFS
        while (count($tiles) > 0) {
            $currentTile = array_shift($tiles);

            if (!in_array($currentTile, $visited)) {
                $visited[] = $currentTile;
            }

            $b = explode(',', $currentTile);

            // Put all adjacent legal board positions relative to current tile in $tiles array
            foreach ($this->offsets as $pq) {
                $p = $b[0] + $pq[0];
                $q = $b[1] + $pq[1];

                $position = $p . "," . $q;

                if (!in_array($position, $visited)) {
                    if (
                        $this->hasNeighbour($board, $position) &&
                        !isset($board[$position])
                    ) {
                        if ($position == $to) {
                            return true;
                        }
                        $tiles[] = $position;
                    }
                }
            }
        }

        return false;
    }

    private function spiderSlide($from, $to): bool
    {
        // TODO
        return true;
    }
}
