<?php

use Mocks\BackendHandlerMock;
use PHPUnit\Framework\TestCase;
use Board\BoardHandler as BoardHandler;

class PieceTest extends TestCase
{
    // Issue #7
    public function testMoveSoldierOneTile() {
        $backendHandler = new BackendHandlerMock();
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
        $backendHandler = new BackendHandlerMock();
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
        $backendHandler = new BackendHandlerMock();
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

    // Issue #8
    public function testMoveSpiderInStraightLine() {
        $backendHandler = new BackendHandlerMock();
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
        $boardHandler->play('B', '2,0');

        // White
        $boardHandler->play('S', '-1,-1');

        // Black
        $boardHandler->play('B', '3,0');

        // White (Move 1 tile, fails with 'Tile must slide' error)
        $boardHandler->move('-1,-1', '0,-1');
        $this->assertArrayNotHasKey('0,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $stateHandler->getBoard());

        // White (Move 2 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,-1', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $stateHandler->getBoard());

        // White (Move 4 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,-1', '3,-1');
        $this->assertArrayNotHasKey('3,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $stateHandler->getBoard());

        // White (Move 5 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,-1', '4,-1');
        $this->assertArrayNotHasKey('4,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $stateHandler->getBoard());

        // White (Move 6 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,-1', '5,-1');
        $this->assertArrayNotHasKey('5,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $stateHandler->getBoard());

        // White (Move 3 tiles)
        $boardHandler->move('-1,-1', '2,-1');
        $this->assertArrayNotHasKey('-1,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('2,-1', $stateHandler->getBoard());
    }

    // Issue #8
    public function testMoveSpiderAroundCorner() {
        $backendHandler = new BackendHandlerMock();
        $boardHandler = new BoardHandler($backendHandler);
        $stateHandler = $backendHandler->getStateHandler();

        $stateHandler->restart();

        // White
        $boardHandler->play('Q', '0,0');

        $boardHandler->play('Q', '1,0');

        // White
        $boardHandler->play('S', '-1,0');

        $boardHandler->play('B', '2,0');

        // White (Move 1 tile, fails with 'Tile must slide' error)
        $boardHandler->move('-1,0', '0,-1');
        $this->assertArrayNotHasKey('0,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $stateHandler->getBoard());

        // White (Move 2 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,0', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $stateHandler->getBoard());

        // White (Move 4 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,0', '3,-1');
        $this->assertArrayNotHasKey('3,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $stateHandler->getBoard());

        // White (Move 5 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,0', '4,-1');
        $this->assertArrayNotHasKey('4,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $stateHandler->getBoard());

        // White (Move 6 tiles, fails with 'Tile must slide' error)
        $boardHandler->move('-1,0', '5,-1');
        $this->assertArrayNotHasKey('5,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $stateHandler->getBoard());

        // White
        $boardHandler->move('-1,0', '2,-1');
        $this->assertArrayNotHasKey('-1,0', $stateHandler->getBoard());
        $this->assertArrayHasKey('2,-1', $stateHandler->getBoard());
    }

    public function testMoveSpiderInSurroundedTiles()
    {
        $backendHandler = new BackendHandlerMock();
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
        $boardHandler->play('A', '2,0');

        // White
        $boardHandler->move('1,-2', '2,-2');

        // Black
        $boardHandler->play('S', '3,-1');

        // White
        $boardHandler->play('A', '1,-2');

        // Black (Fails with 'Tile must slide') error
        $boardHandler->move('3,-1', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('3,-1', $stateHandler->getBoard());

        // Black (Move 3 tiles)
        $boardHandler->move('3,-1', '1,1');
        $this->assertArrayNotHasKey('3,-1', $stateHandler->getBoard());
        $this->assertArrayHasKey('1,1', $stateHandler->getBoard());
    }
}