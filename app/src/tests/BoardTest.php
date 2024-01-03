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

    // Issue #2
    public function testMoveToUninitializedPosition() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');

        // Black
        $boardHandler->play('Q', '1,0');

        // White
        $boardHandler->move('0,0', '0,1');
        $this->assertArrayHasKey('0,1', $stateHandler->getBoard());
    }

    // Issue #3
    public function testPlayAfterMustPlaceQueenError() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('A', '0,0');

        // Black
        $boardHandler->play('A', '1,0');

        // White
        $boardHandler->play('A', '-1,0');

        // Black
        $boardHandler->play('A', '2,0');

        // White
        $boardHandler->play('A', '-2,0');

        // Black
        $boardHandler->play('A', '3,0');

        // White (Fails with 'Must play queen bee')
        $boardHandler->play('B', '-3,0');
        $this->assertArrayNotHasKey('-3,0', $stateHandler->getBoard());

        // White
        $boardHandler->play('Q', '-3,0');
        $this->assertArrayHasKey('-3,0', $stateHandler->getBoard());

        // Black (Fails with 'Must play queen bee')
        $boardHandler->play('B', '4,0');
        $this->assertArrayNotHasKey('4.0', $stateHandler->getBoard());

        // Black
        $boardHandler->play('Q', '4,0');
        $this->assertArrayHasKey('4,0', $stateHandler->getBoard());

        // White (For good measure)
        $boardHandler->play('B', '-4,0');
        $this->assertArrayHasKey('-4,0', $stateHandler->getBoard());
    }
}
