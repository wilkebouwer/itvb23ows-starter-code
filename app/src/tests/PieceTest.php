<?php

use Fake\BackendHandlerMock;
use PHPUnit\Framework\TestCase;
use Board\BoardHandler as BoardHandler;
use AIConnection\AIConnectionHandler as AIConnectionHandler;
use State\StateHandler as StateHandler;

class PieceTest extends TestCase
{
    private BoardHandler $boardHandler;
    private StateHandler $stateHandler;

    protected function setUp(): void
    {
        $backendHandler = new BackendHandlerMock();
        $this->boardHandler = new BoardHandler($backendHandler, new AIConnectionHandler());
        $this->stateHandler = $backendHandler->getStateHandler();

        $backendHandler->restart();
    }

    // Issue #7
    public function testMoveAntOneTile() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('A', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('-1,0', '0,-1');
        $this->assertArrayNotHasKey('-1,0', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,-1', $this->stateHandler->getBoard());
    }

    // Issue #7
    public function testMoveAntMultipleTiles() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('A', '-1,0');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White
        $this->boardHandler->move('-1,0', '1,1');
        $this->assertArrayNotHasKey('-1,0', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('1,1', $this->stateHandler->getBoard());

        // Black
        $this->boardHandler->move('2,0', '-1,0');
        $this->assertArrayNotHasKey('2,0', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());
    }

    // Issue #7
    public function testMoveAntInSurroundedTiles() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('B', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,-1');

        // White
        $this->boardHandler->play('A', '0,-1');

        // Black
        $this->boardHandler->play('B', '3,-2');

        // White
        $this->boardHandler->play('B', '1,-2');

        // Black
        $this->boardHandler->play('S', '2,0');

        // White
        $this->boardHandler->move('1,-2', '2,-2');

        // Black
        $this->boardHandler->play('A', '3,-1');

        // White
        $this->boardHandler->play('S', '1,-2');

        // Black (Fails with 'Tile must slide') error
        $this->boardHandler->move('3,-1', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('3,-1', $this->stateHandler->getBoard());

        // Black
        $this->boardHandler->move('3,-1', '-1,-1');
        $this->assertArrayNotHasKey('3,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMoveSpiderInStraightLine() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('B', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->play('S', '-1,-1');

        // Black
        $this->boardHandler->play('B', '3,0');

        // White (Move 1 tile, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,-1', '0,-1');
        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $this->stateHandler->getBoard());

        // White (Move 2 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,-1', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $this->stateHandler->getBoard());

        // White (Move 4 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,-1', '3,-1');
        $this->assertArrayNotHasKey('3,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $this->stateHandler->getBoard());

        // White (Move 5 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,-1', '4,-1');
        $this->assertArrayNotHasKey('4,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $this->stateHandler->getBoard());

        // White (Move 6 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,-1', '5,-1');
        $this->assertArrayNotHasKey('5,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,-1', $this->stateHandler->getBoard());

        // White (Move 3 tiles)
        $this->boardHandler->move('-1,-1', '2,-1');
        $this->assertArrayNotHasKey('-1,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('2,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMoveSpiderAroundCorner() {
        // White
        $this->boardHandler->play('Q', '0,0');

        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('S', '-1,0');

        $this->boardHandler->play('B', '2,0');

        // White (Move 1 tile, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '0,-1');
        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());

        // White (Move 2 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());

        // White (Move 4 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '3,-1');
        $this->assertArrayNotHasKey('3,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());

        // White (Move 5 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '4,-1');
        $this->assertArrayNotHasKey('4,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());

        // White (Move 6 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '5,-1');
        $this->assertArrayNotHasKey('5,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('-1,0', '2,-1');
        $this->assertArrayNotHasKey('-1,0', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('2,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMoveSpiderInSurroundedTiles()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('B', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,-1');

        // White
        $this->boardHandler->play('A', '0,-1');

        // Black
        $this->boardHandler->play('B', '3,-2');

        // White
        $this->boardHandler->play('B', '1,-2');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White
        $this->boardHandler->move('1,-2', '2,-2');

        // Black
        $this->boardHandler->play('S', '3,-1');

        // White
        $this->boardHandler->play('A', '1,-2');

        // Black (Fails with 'Tile must slide' error)
        $this->boardHandler->move('3,-1', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('3,-1', $this->stateHandler->getBoard());

        // Black (Move 3 tiles)
        $this->boardHandler->move('3,-1', '1,1');
        $this->assertArrayNotHasKey('3,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('1,1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMoveGrasshopperHorizontal() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '2,-1');
        $this->assertArrayNotHasKey('2,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('-1,0', '3,0');
        $this->assertArrayNotHasKey('-1,0', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('3,0', $this->stateHandler->getBoard());

        // Black
        $this->boardHandler->play('B', '2,-1');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('3,0', '0,1');
        $this->assertArrayNotHasKey('0,1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('3,0', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('3,0', '-1,0');
        $this->assertArrayNotHasKey('3,0', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMoveGrasshopperTLBR() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '0,-1');

        //Black
        $this->boardHandler->play('B', '2,0');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('0,-1', '1,1');
        $this->assertArrayNotHasKey('1,1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,-1', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('0,-1', '0,1');
        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,1', $this->stateHandler->getBoard());

        // Black
        $this->boardHandler->play('B', '3,0');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('0,1', '1,-1');
        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,1', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('0,1', '0,-1');
        $this->assertArrayNotHasKey('0,1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,-1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMoveGrasshopperTRBL()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '-1,0');

        // White
        $this->boardHandler->play('S', '1,-1');

        // Black
        $this->boardHandler->play('B', '-2,0');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('1,-1', '-2,1');
        $this->assertArrayNotHasKey('-2,1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('1,-1', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('1,-1', '-1,1');
        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,1', $this->stateHandler->getBoard());

        // Black
        $this->boardHandler->play('B', '-3,0');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,1', '0,-1');
        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('-1,1', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('-1,1', '1,-1');
        $this->assertArrayNotHasKey('-1,1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('1,-1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMoveGrasshopperOverEmptySpace() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '0,-1');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White
        $this->boardHandler->play('B', '-1,0');

        // Black
        $this->boardHandler->move('2,0', '-1,1');

        // White
        $this->boardHandler->play('B', '-2,0');

        // Black
        $this->boardHandler->play('B', '-1,2');

        // White
        $this->boardHandler->play('S', '-3,0');

        // Black
        $this->boardHandler->play('B', '0,2');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('0,-1', '0,3');
        $this->assertArrayNotHasKey('0,3', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,-1', $this->stateHandler->getBoard());

        // White
        $this->boardHandler->move('0,-1', '0,1');
        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMoveGrasshopperToNeighbouringEmptyPosition() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // White
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '0,-1');

        // White
        $this->boardHandler->play('B', '2,0');

        // White (Fails with 'Tile must slide' error)
        $this->boardHandler->move('0,-1', '-1,0');
        $this->assertArrayNotHasKey('-1,0', $this->stateHandler->getBoard());
        $this->assertArrayHasKey('0,-1', $this->stateHandler->getBoard());
    }
}
