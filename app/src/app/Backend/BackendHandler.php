<?php

namespace Backend;

use Database\DatabaseHandler;
use State\StateHandler;

class BackendHandler
{

    protected DatabaseHandler $databaseHandler;
    protected StateHandler $stateHandler;

    public function __construct()
    {
        $this->databaseHandler = new DatabaseHandler();
        $this->stateHandler = new StateHandler();
    }

    public function getStateHandler(): StateHandler
    {
        return $this->stateHandler;
    }

    public function restart() {
        $this->stateHandler->restart();

        $this->databaseHandler->addNewGame();
        $this->stateHandler->setGameID($this->databaseHandler->getInsertID());
    }

    public function undo()
    {
        $lastMoveID = $this->stateHandler->getLastMove();

        if ($lastMoveID === null) {
            $this->databaseHandler->deleteMoves($this->stateHandler->getGameID());

            return;
        }

        // Get last move ID from database
        $previousMoveID = $this->databaseHandler
            ->getMove($lastMoveID)
            ->fetch_array()[5];

        if ($previousMoveID == null) {
            $this->databaseHandler->deleteMoves($this->stateHandler->getGameID());

            $this->stateHandler->restart();

            return;
        }

        // Get previous move from database as an array
        $previousMoveArray = $this->databaseHandler
            ->getMove($previousMoveID)
            ->fetch_array();

        // Remove last move from database
        $this->databaseHandler->deleteMove($lastMoveID);

        // Set last move to last move's last move
        $this->stateHandler->setLastMove($previousMoveID);

        // Set current state to state of previous move
        $this->stateHandler->setStateFromSerialized($previousMoveArray[6]);

        // Switch player
        $this->stateHandler->switchPlayer();
    }

    public function addMove($from, $to)
    {
        $this->databaseHandler->addMove(
            "issis",
            $this->stateHandler->getGameID(),
            $from,
            $to,
            $this->stateHandler->getLastMove(),
            $this->stateHandler->getSerializedState()
        );

        // Set last move
        $this->stateHandler->setLastMove($this->databaseHandler->getInsertID());

        // Change to different player
        $this->stateHandler->switchPlayer();
    }

    public function getMoves()
    {
        return $this->databaseHandler->getMoves($this->stateHandler->getGameID());
    }
}
