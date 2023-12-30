<?php

namespace Backend;

use Database\DatabaseHandler;
use State\StateHandler;

class BackendHandler
{

    private DatabaseHandler $databaseHandler;
    private StateHandler $stateHandler;

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
        // Get last move from database as an array
        $lastMoveArray = $this->databaseHandler->
        getLastMove($this->stateHandler->getLastMove())
            ->fetch_array();

        // Set last move to last move's last move
        $this->stateHandler->setLastMove($lastMoveArray[5]);

        // Set current state to state of last move
        $this->stateHandler->setStateFromSerialized($lastMoveArray[6]);
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
