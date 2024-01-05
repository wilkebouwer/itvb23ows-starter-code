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
    // Issue #1
    public function testOnlyGetAvailablePiecesForPlayer() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $this->assertContains('Q', $boardHandler->getAvailableHandPieces());
        $boardHandler->play('Q', '0,0');

        // Black
        $this->assertContains('Q', $boardHandler->getAvailableHandPieces());
        $boardHandler->play('Q', '1,0');

        // White
        $this->assertNotContains('Q', $boardHandler->getAvailableHandPieces());
        $boardHandler->play('B', '-1,0');

        // Black
        $this->assertNotContains('Q', $boardHandler->getAvailableHandPieces());
    }

    // Issue #1
    public function testOnlyGetAvailableMovePositionsForPlayer() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('A', '0,0');

        // Black
        $this->assertNotContains('0,0', $boardHandler->getPlayerPiecePositions());
        $boardHandler->play('A', '1,0');

        // White
        $this->assertContains('0,0', $boardHandler->getPlayerPiecePositions());
        $this->assertNotContains('1,0', $boardHandler->getPlayerPiecePositions());
        $boardHandler->play('A', '-1,0');

        // Black
        $this->assertContains('1,0', $boardHandler->getPlayerPiecePositions());
        $this->assertNotContains('-1,0', $boardHandler->getPlayerPiecePositions());
        $boardHandler->play('A', '2,0');

        // White
        $this->assertContains('-1,0', $boardHandler->getPlayerPiecePositions());
        $this->assertNotContains('2,0', $boardHandler->getPlayerPiecePositions());
        $boardHandler->play('A', '-2,0');
    }

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

    // Issue #4
    public function testPlaceTileOnPreviousMovePosition() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');

        // Black
        $boardHandler->play('Q', '1,0');

        // White
        $boardHandler->play('A', '-1,0');

        // Black
        $boardHandler->play('A', '2,0');

        // White
        $boardHandler->move('-1,0', '0,-1');
        $this->assertArrayNotHasKey('-1,0', $stateHandler->getBoard());
        $this->assertArrayHasKey('0,-1', $stateHandler->getBoard());

        // Black
        $boardHandler->move('2,0', '2,-1');
        $this->assertArrayNotHasKey('2,0', $stateHandler->getBoard());
        $this->assertArrayHasKey('2,-1', $stateHandler->getBoard());

        // White
        $boardHandler->play('S', '-1,0');
        $this->assertArrayHasKey('-1,0', $stateHandler->getBoard());

        // Black
        $boardHandler->play('S', '2,0');
        $this->assertArrayHasKey('2,0', $stateHandler->getBoard());
    }

    public function testWhiteWin() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('Q', '1,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('A', '-1,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('B', '1,1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('A', '-2,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('B', '2,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->move('-2,0', '1,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('A', '2,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White (Winning move)
        $boardHandler->move('-1,0', '0,1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertTrue($boardHandler->lostGame(1));
    }

    public function testBlackWin() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('Q', '1,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('B', '-1,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('A', '2,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('B', '0,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('A', '3,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('S', '-1,1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->move('3,0', '1,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('A', '-1,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black (Winning move)
        $boardHandler->move('2,0', '0,1');
        $this->assertTrue($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));
    }

    public function testDraw() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('Q', '1,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('B', '-1,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('B', '2,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('A', '-2,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('A', '3,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('A', '-3,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('A', '4,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->play('A', '-4,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->play('A', '5,0');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->move('-4,0', '0,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->move('5,0', '2,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->move('-3,0', '-1,1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black
        $boardHandler->move('4,0', '1,1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // White
        $boardHandler->move('-2,0', '1,-1');
        $this->assertFalse($boardHandler->lostGame(0));
        $this->assertFalse($boardHandler->lostGame(1));

        // Black (Draw move)
        $boardHandler->move('3,0', '0,1');
        $this->assertTrue($boardHandler->lostGame(0));
        $this->assertTrue($boardHandler->lostGame(1));
    }

    // Issue #7
    public function testMoveSoldierOneTile() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');

        // Black
        $boardHandler->play('Q', '1,0');

        // White
        $boardHandler->play('A', '-1,0');

        // Black
        $boardHandler->play('B', '2,0');

        // White
        $boardHandler->move('-1,0', '0,-1');
        $this->assertArrayNotHasKey('-1,0', $stateHandler->getBoard());
        $this->assertArrayHasKey('0,-1', $stateHandler->getBoard());
    }

    // Issue #7
    public function testMoveSoldierMultipleTiles() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');

        // Black
        $boardHandler->play('Q', '1,0');

        // White
        $boardHandler->play('A', '-1,0');

        // Black
        $boardHandler->play('A', '2,0');

        // White
        $boardHandler->move('-1,0', '1,1');
        $this->assertArrayNotHasKey('-1,0', $stateHandler->getBoard());
        $this->assertArrayHasKey('1,1', $stateHandler->getBoard());

        // Black
        $boardHandler->move('2,0', '-1,0');
        $this->assertArrayNotHasKey('2,0', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $stateHandler->getBoard());
    }

    // Issue #7
    public function testMoveSoldierInSurroundedTiles() {
        $backendHandler = new BackendHandlerTester();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');

        // Black
        $boardHandler->play('Q', '1,0');

        // White
        $boardHandler->play('B', '-1,0');

        // Black
        $boardHandler->play('B', '2,-1');

        // White
        $boardHandler->play('A', '0,-1');

        // Black
        $boardHandler->play('B', '3,-2');

        // White
        $boardHandler->play('B', '1,-2');

        // Black
        $boardHandler->play('S', '2,0');

        // White
        $boardHandler->move('1,-2', '2,-2');

        // Black
        $boardHandler->play('A', '3,-1');

        // White
        $boardHandler->play('S', '1,-2');

        // Black (Fails with 'Tile must slide') error
        $boardHandler->move('3,-1', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('3,-1', $stateHandler->getBoard());

        // Black
        $boardHandler->move('3,-1', '-1,-1');
        $this->assertArrayNotHasKey('3,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $stateHandler->getBoard());
    }
}
