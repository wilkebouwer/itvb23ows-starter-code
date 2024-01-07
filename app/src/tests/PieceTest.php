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
    public function testMove_MoveAntOneTile_MoveInState()
    {
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
    }

    // Issue #7
    public function testMove_MoveAntMultipleTiles_MoveInState()
    {
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

        $this->assertArrayHasKey('1,1', $this->stateHandler->getBoard());
    }

    // Issue #7
    public function testMove_MoveAntInSurroundedTiles_MoveNotInState()
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
    }

    // Issue #8
    public function testMove_MoveSpiderOneTileInStraightLine_MoveNotInState()
    {
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

        // White (Move 1 tiles)
        $this->boardHandler->move('-1,-1', '0,-1');

        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderTwoTilesInStraightLine_MoveNotInState()
    {
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

        // White (Move 2 tiles)
        $this->boardHandler->move('-1,-1', '1,-1');

        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderThreeTilesInStraightLine_MoveInState()
    {
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

        // White (Move 3 tiles)
        $this->boardHandler->move('-1,-1', '2,-1');

        $this->assertArrayHasKey('2,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderFourTilesInStraightLine_MoveNotInState()
    {
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

        // White (Move 1 tiles)
        $this->boardHandler->move('-1,-1', '3,-1');

        $this->assertArrayNotHasKey('3,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderOneTileAroundCorner_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('S', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White (Move 1 tile, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '0,-1');

        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderTwoTilesAroundCorner_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('S', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White (Move 2 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '1,-1');

        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderThreeTilesAroundCorner_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('S', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('-1,0', '2,-1');

        $this->assertArrayHasKey('2,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderFourTilesAroundCorner_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('S', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White (Move 4 tiles, fails with 'Tile must slide' error)
        $this->boardHandler->move('-1,0', '3,-1');

        $this->assertArrayNotHasKey('3,-1', $this->stateHandler->getBoard());
    }

    // Issue #8
    public function testMove_MoveSpiderInSurroundedTiles_MoveNotInState()
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
    }

    // Issue #6
    public function testMove_MoveGrasshopperLeftToRight_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('-1,0', '3,0');

        $this->assertArrayHasKey('3,0', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperToNeighbourOfLeftToRight_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '-1,0');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('-1,0', '3,-1');

        $this->assertArrayNotHasKey('3,-1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperRightToLeft_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '-1,0');

        // White
        $this->boardHandler->play('G', '1,0');

        // Black
        $this->boardHandler->play('B', '-2,0');

        // White
        $this->boardHandler->move('1,0', '-3,0');

        $this->assertArrayHasKey('-3,0', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperToNeighbourOfRightToLeft_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '-1,0');

        // White
        $this->boardHandler->play('G', '1,0');

        // Black
        $this->boardHandler->play('B', '-2,0');

        // White
        $this->boardHandler->move('1,0', '-3,1');

        $this->assertArrayNotHasKey('-3,1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperTopLeftToBottomRight_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '0,-1');

        //Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('0,-1', '0,1');

        $this->assertArrayHasKey('0,1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperToNeighbourOfTopLeftToBottomRight_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '0,-1');

        //Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('0,-1', '-1,1');

        $this->assertArrayNotHasKey('-1,1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperBottomRightToTopLeft_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '-1,0');

        // White
        $this->boardHandler->play('G', '0,1');

        //Black
        $this->boardHandler->play('B', '-2,0');

        // White
        $this->boardHandler->move('0,1', '0,-1');

        $this->assertArrayHasKey('0,-1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperToNeighbourOfBottomRightToTopLeft_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '-1,0');

        // White
        $this->boardHandler->play('G', '0,1');

        //Black
        $this->boardHandler->play('B', '-2,0');

        // White
        $this->boardHandler->move('0,1', '1,-1');

        $this->assertArrayNotHasKey('1,-1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperTopRightToBottomLeft_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '-1,0');

        // White
        $this->boardHandler->play('G', '1,-1');

        // Black
        $this->boardHandler->play('B', '-2,0');

        // White
        $this->boardHandler->move('1,-1', '-1,1');

        $this->assertArrayHasKey('-1,1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperToNeighbourOfTopRightToBottomLeft_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '-1,0');

        // White
        $this->boardHandler->play('G', '1,-1');

        // Black
        $this->boardHandler->play('B', '-2,0');

        // White
        $this->boardHandler->move('1,-1', '0,1');

        $this->assertArrayNotHasKey('0,1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperBottomLeftToTopRight_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '-1,1');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('-1,1', '1,-1');

        $this->assertArrayHasKey('1,-1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperToNeighbourOfBottomLeftToTopRight_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('G', '-1,1');

        // Black
        $this->boardHandler->play('B', '2,0');

        // White
        $this->boardHandler->move('-1,1', '0,-1');

        $this->assertArrayNotHasKey('0,-1', $this->stateHandler->getBoard());
    }

    // Issue #6
    public function testMove_MoveGrasshopperOverEmptySpace_MoveNotInState()
    {
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
    }

    // Issue #6
    public function testMove_MoveGrasshopperToNeighbouringEmptyPosition_MoveNotInState()
    {
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
    }
}
