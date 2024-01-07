<?php

namespace Fake;

use Database\DatabaseHandler;

class DatabaseHandlerMock extends DatabaseHandler
{
    private array $gameTable;
    private array $moveTable;
    private int $insertID;

    public function __construct()
    {
        $this->gameTable = [];
        $this->moveTable = [];
    }

    public function addMove($types, $gameId, $from, $to, $move, $state)
    {
        $nextMoveID = count($this->moveTable) + 1;

        $this->moveTable[] = [$nextMoveID, $gameId, "move", $from, $to, $move, $state];

        $this->insertID = $nextMoveID;
    }

    public function deleteMove($id) {
       unset($this->moveTable[$id - 1]);
    }

    public function deleteMoves($gameID) {
        for ($i = 0; $i < count($this->moveTable); $i++) {
            if ($this->moveTable[$i][1] == $gameID) {
                unset($this->moveTable[$i]);
            }
        }
    }

    public function getMoves($gameID): array
    {
        $result = [];
        foreach($this->moveTable as $move) {
            if ($move[1] == $gameID) {
                $result[] = $move;
            }
        }

        return $result;
    }

    public function getMove($id): object
    {
        return new class($this->moveTable, $id)
        {
            private array $moveTable;
            private string $id;

            public function __construct($moveTable, $id)
            {
                $this->moveTable = $moveTable;
                $this->id = $id;
            }

            // Mock mysqli_result internal function
            function fetch_array()
            {
                return $this->moveTable[$this->id - 1];
            }
        };
    }

    public function addNewGame() {
        $nextGameID = count($this->gameTable) + 1;

        $this->gameTable[] = $nextGameID;

        $this->insertID = $nextGameID;
    }

    public function getInsertID(): int
    {
        return $this->insertID;
    }
}