<?php

use PHPUnit\Framework\TestCase;

use Backend\BackendHandler as BackendHandler;
use Board\BoardHandler as BoardHandler;
use State\StateHandler as StateHandler;

class BackendHandlerTester extends BackendHandler {

    public function __construct()
    {
        $this->stateHandler = new StateHandler();
    }

    public function addMove($from, $to)
    {
        // Change to different player
        $this->stateHandler->switchPlayer();
    }
}

class BoardTest extends TestCase
{
    public function testMoveWhiteQueen() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play(
            $stateHandler->getBoard(),
            $stateHandler->getPlayer(),
            'Q',
            '0,0'
        );

        // Black
        $boardHandler->play(
            $stateHandler->getBoard(),
            $stateHandler->getPlayer(),
            'Q',
            '1,0'
        );

        // White
        $boardHandler->move(
            $stateHandler->getBoard(),
            $stateHandler->getPlayer(),
            '0,0',
            '0,1'
        );

        $this->assertArrayHasKey('0,1', $stateHandler->getBoard());
    }
}