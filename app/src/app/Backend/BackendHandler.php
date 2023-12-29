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

    public function setMove($from, $to)
    {
        if ($from != null) {
            $fromType = 's';
        } else {
            $fromType = '';
        }
        if ($to != null) {
            $toType = 's';
        } else {
            $toType = '';
        }

        // String of all statement variable types
        $types = 'i' . $fromType . $toType . 'is';

        $this->databaseHandler->setMove(
            $types,
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
}