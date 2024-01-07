<?php


use Fake\BackendHandlerMock;
use PHPUnit\Framework\TestCase;
use Board\BoardHandler as BoardHandler;
use AIConnection\AIConnectionHandler as AIConnectionHandler;
use State\StateHandler as StateHandler;

class BoardTest extends TestCase
{
    private BackendHandlerMock $backendHandler;
    private BoardHandler $boardHandler;
    private StateHandler $stateHandler;

    protected function setUp(): void
    {
        $this->backendHandler = new BackendHandlerMock();
        $this->boardHandler = new BoardHandler($this->backendHandler, new AIConnectionHandler());
        $this->stateHandler = $this->backendHandler->getStateHandler();

        $this->backendHandler->restart();
    }

    // Issue #1
    public function testPlay_GetAvailablePiecesForWhite_QueenInAvailablePieces()
    {
        // White
        $availableHandPieces = $this->boardHandler->getAvailableHandPieces();

        $this->assertContains('Q', $availableHandPieces);
    }

    // Issue #1
    public function testPlay_GetAvailablePiecesForBlack_QueenInAvailablePieces()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $availableHandPieces = $this->boardHandler->getAvailableHandPieces();

        $this->assertContains('Q', $availableHandPieces);
    }

    // Issue #1
    public function testPlay_GetAvailablePiecesForWhite_QueenNotInAvailablePieces()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        $availableHandPieces = $this->boardHandler->getAvailableHandPieces();

        // White
        $this->assertNotContains('Q', $availableHandPieces);
    }

    // Issue #1
    public function testPlay_GetAvailablePiecesForBlack_QueenNotInAvailablePieces()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('B', '-1,0');

        // Black
        $this->assertNotContains('Q', $this->boardHandler->getAvailableHandPieces());
    }

    // Issue #1
    public function testPlay_GetAvailableMovePositionsForBlackAtFirstMove_LastWhitePositionNotInPositionsList()
    {
        // White
        $this->boardHandler->play('A', '0,0');

        // Black
        $this->assertNotContains('0,0', $this->boardHandler->getPlayerPiecePositions());
    }

    // Issue #1
    public function testPlay_GetAvailableMovePositionsForWhiteAtFirstMove_LastBlackPositionNotInPositionsList()
    {
        // White
        $this->boardHandler->play('A', '0,0');

        // Black
        $this->boardHandler->play('A', '1,0');

        // White
        $this->assertNotContains('1,0', $this->boardHandler->getPlayerPiecePositions());
    }

    // Issue #1
    public function testPlay_GetAvailableMovePositionsForWhiteAtFirstMove_LastWhitePositionInPositionsList()
    {
        // White
        $this->boardHandler->play('A', '0,0');

        // Black
        $this->boardHandler->play('A', '1,0');

        // White
        $this->assertContains('0,0', $this->boardHandler->getPlayerPiecePositions());
    }

    // Issue #1
    public function testPlay_GetAvailableMovePositionsForBlackAtSecondMove_LastWhitePositionNotInPositionsList()
    {
        // White
        $this->boardHandler->play('A', '0,0');

        // Black
        $this->boardHandler->play('A', '1,0');

        // White
        $this->boardHandler->play('A', '-1,0');

        // Black
        $this->assertNotContains('-1,0', $this->boardHandler->getPlayerPiecePositions());
    }

    // Issue #1
    public function test_GetAvailableMovePositionsForBlackAtSecondMove_LastBlackPositionInPositionsList()
    {
        // White
        $this->boardHandler->play('A', '0,0');

        // Black
        $this->boardHandler->play('A', '1,0');

        // White
        $this->boardHandler->play('A', '-1,0');

        // Black
        $this->assertContains('1,0', $this->boardHandler->getPlayerPiecePositions());
    }

    // Issue #2
    public function testMove_MoveToUninitializedPosition_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->move('0,0', '0,1');

        $this->assertArrayHasKey('0,1', $this->stateHandler->getBoard());
    }

    // Issue #3
    public function testPlay_WhiteGetsPlaceQueenError_MoveNotInState()
    {
        // White
        $this->boardHandler->play('A', '0,0');

        // Black
        $this->boardHandler->play('A', '1,0');

        // White
        $this->boardHandler->play('A', '-1,0');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White
        $this->boardHandler->play('A', '-2,0');

        // Black
        $this->boardHandler->play('A', '3,0');

        // White (Fails with 'Must play queen bee')
        $this->boardHandler->play('B', '-3,0');

        $this->assertArrayNotHasKey('-3,0', $this->stateHandler->getBoard());
    }

    // Issue #3
    public function testPlay_BlackPlaysAfterMustPlaceQueenError_MoveInState()
    {
        // White
        $this->boardHandler->play('A', '0,0');

        // Black
        $this->boardHandler->play('A', '1,0');

        // White
        $this->boardHandler->play('A', '-1,0');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White
        $this->boardHandler->play('A', '-2,0');

        // Black
        $this->boardHandler->play('A', '3,0');

        // White (Fails with 'Must play queen bee')
        $this->boardHandler->play('B', '-3,0');

        // White
        $this->boardHandler->play('Q', '-3,0');

        $this->assertArrayHasKey('-3,0', $this->stateHandler->getBoard());
    }

    // Issue #4
    public function testPlay_WhitePlacesTileOnPositionThatPreviouslyMoved_MoveInState()
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
        $this->boardHandler->move('-1,0', '0,-1');

        // Black
        $this->boardHandler->move('2,0', '2,-1');

        // White
        $this->boardHandler->play('S', '-1,0');

        $this->assertArrayHasKey('-1,0', $this->stateHandler->getBoard());
    }

    // Issue #4
    public function testPlay_BlackPlacesTileOnPositionThatPreviouslyMoved_MoveInState()
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
        $this->boardHandler->move('-1,0', '0,-1');

        // Black
        $this->boardHandler->move('2,0', '2,-1');

        // White
        $this->boardHandler->play('S', '-1,0');

        // Black
        $this->boardHandler->play('S', '2,0');

        $this->assertArrayHasKey('2,0', $this->stateHandler->getBoard());
    }

    // Issue #10
    public function testMove_WhiteMakesWinningMove_BlackLost()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('A', '-1,0');

        // Black
        $this->boardHandler->play('B', '1,1');

        // White
        $this->boardHandler->play('A', '-2,0');

        // Black
        $this->boardHandler->play('B', '2,-1');

        // White
        $this->boardHandler->move('-2,0', '1,-1');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White (Winning move)
        $this->boardHandler->move('-1,0', '0,1');

        $this->assertTrue($this->boardHandler->lostGame(1));
    }

    // Issue #10
    public function testMove_BlackMakesWinningMove_WhiteLost()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('B', '-1,0');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White
        $this->boardHandler->play('B', '0,-1');

        // Black
        $this->boardHandler->play('A', '3,0');

        // White
        $this->boardHandler->play('S', '-1,1');

        // Black
        $this->boardHandler->move('3,0', '1,-1');

        // White
        $this->boardHandler->play('A', '-1,-1');

        // Black (Winning move)
        $this->boardHandler->move('2,0', '0,1');

        $this->assertTrue($this->boardHandler->lostGame(0));
    }

    // Issue #10
    public function testMove_MakeDrawMove_WhiteLost()
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
        $this->boardHandler->play('A', '-2,0');

        // Black
        $this->boardHandler->play('A', '3,0');

        // White
        $this->boardHandler->play('A', '-3,0');

        // Black
        $this->boardHandler->play('A', '4,0');

        // White
        $this->boardHandler->play('A', '-4,0');

        // Black
        $this->boardHandler->play('A', '5,0');

        // White
        $this->boardHandler->move('-4,0', '0,-1');

        // Black
        $this->boardHandler->move('5,0', '2,-1');

        // White
        $this->boardHandler->move('-3,0', '-1,1');

        // Black
        $this->boardHandler->move('4,0', '1,1');

        // White
        $this->boardHandler->move('-2,0', '1,-1');

        // Black (Draw move)
        $this->boardHandler->move('3,0', '0,1');

        $this->assertTrue($this->boardHandler->lostGame(0));
    }

    // Issue #10
    public function testMove_MakeDrawMove_BlackLost()
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
        $this->boardHandler->play('A', '-2,0');

        // Black
        $this->boardHandler->play('A', '3,0');

        // White
        $this->boardHandler->play('A', '-3,0');

        // Black
        $this->boardHandler->play('A', '4,0');

        // White
        $this->boardHandler->play('A', '-4,0');

        // Black
        $this->boardHandler->play('A', '5,0');

        // White
        $this->boardHandler->move('-4,0', '0,-1');

        // Black
        $this->boardHandler->move('5,0', '2,-1');

        // White
        $this->boardHandler->move('-3,0', '-1,1');

        // Black
        $this->boardHandler->move('4,0', '1,1');

        // White
        $this->boardHandler->move('-2,0', '1,-1');

        // Black (Draw move)
        $this->boardHandler->move('3,0', '0,1');

        $this->assertTrue($this->boardHandler->lostGame(1));
    }

    // Issue #9
    public function testMove_OtherPlayerMovesAfterPass_MoveInState() {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->play('B', '-1,0');

        // Black
        $this->boardHandler->play('A', '2,0');

        // White
        $this->boardHandler->play('B', '-2,0');

        // Black
        $this->boardHandler->play('A', '3,0');

        // White
        $this->boardHandler->play('G', '-3,0');

        // Black
        $this->boardHandler->play('A', '4,0');

        // White
        $this->boardHandler->play('G', '-4,0');

        // Black
        $this->boardHandler->play('S', '5,0');

        // White
        $this->boardHandler->play('G', '-5,0');

        // Black
        $this->boardHandler->play('S', '6,0');

        // White
        $this->boardHandler->play('S', '-6,0');

        // Black
        $this->boardHandler->play('G', '7,0');

        // White
        $this->boardHandler->play('S', '-7,0');

        // Black
        $this->boardHandler->play('G', '8,0');

        // White
        $this->boardHandler->play('A', '-8,0');

        // Black
        $this->boardHandler->play('G', '9,0');

        // White
        $this->boardHandler->play('A', '-9,0');

        // Black
        $this->boardHandler->play('B', '10,0');

        // White
        $this->boardHandler->play('A', '-10,0');

        // Black
        $this->boardHandler->play('B', '11,0');

        // White
        $this->boardHandler->move('-10,0', '12,0');

        // Black
        $this->boardHandler->pass();

        // White (Can make move because of black's successful pass)
        $this->boardHandler->move('-9,0', '13,0');

        $this->assertArrayHasKey('13,0', $this->stateHandler->getBoard());
    }

    // Issue #5
    public function testPlay_PlayAfterOneTilePlaceUndo_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        $this->backendHandler->undo();

        // White
        $this->boardHandler->play('Q', '0,0');

        $this->assertArrayHasKey('0,0', $this->stateHandler->getBoard());
    }

    // Issue #5
    public function testPlay_PlayAfterOneTilePlaceUndo_MoveInDatabase()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        $this->backendHandler->undo();

        // White
        $this->boardHandler->play('Q', '0,0');

        $this->assertEquals('0,0', $this->backendHandler->getMoves()->fetch_array()[4]);
    }

    // Issue #5
    public function testPlay_PlayAfterTwoTilesPlaceOneUndo_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->backendHandler->undo();

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->assertArrayHasKey('1,0', $this->stateHandler->getBoard());
    }

    // Issue #5
    public function testPlay_PlayAfterTwoTilesPlaceOneUndo_MoveInDatabase()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->backendHandler->undo();

        // Black
        $this->boardHandler->play('Q', '-1,0');

        $moves = $this->backendHandler->getMoves();

        $moves->fetch_array();

        $this->assertEquals('-1,0', $moves->fetch_array()[4]);
    }


    // Issue #5
    public function testUndo_UndoImmediately_MoveNotInState()
    {
        // White
        $this->backendHandler->undo();

        $this->assertEmpty($this->stateHandler->getBoard());
    }

    // Issue #5
    public function testUndo_UndoImmediately_MoveNotInDatabase()
    {
        // White
        $this->backendHandler->undo();

        $this->assertEmpty($this->stateHandler->getBoard());
    }

    // Issue #5
    public function testUndo_UndoTwiceOnePlacedTile_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->assertEmpty($this->stateHandler->getBoard());
    }

    // Issue #5
    public function testUndo_UndoTwiceOnePlacedTile_MoveNotInDatabase()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->assertEmpty($this->backendHandler->getMoves()->fetch_array());
    }

    // Issue #5
    public function testUndo_UndoOnePlacedTile_MoveNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        $this->backendHandler->undo();

        $this->assertArrayNotHasKey('0,0', $this->stateHandler->getBoard());
    }

    // Issue #5
    public function testUndo_UndoOnePlacedTile_MoveNotInDatabase()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        $this->backendHandler->undo();

        $this->assertEmpty($this->backendHandler->getMoves()->fetch_array());
    }

    // Issue #5
    public function testUndo_OneUndoTwoPlacedTiles_MoveInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->backendHandler->undo();

        $this->assertArrayHasKey('0,0', $this->stateHandler->getBoard());
    }

    // Issue #5
    public function testUndo_OneUndoTwoPlacedTiles_MoveInDatabase()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->backendHandler->undo();

        $this->assertEquals('0,0', $this->backendHandler->getMoves()->fetch_array()[4]);
    }

    // Issue #5
    public function testUndo_TwoUndoTwoPlacedTiles_MovesNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->assertArrayNotHasKey('0,0', $this->stateHandler->getBoard());
    }

    // Issue #5
    public function testUndo_TwoUndoTwoPlacedTiles_MovesNotInDatabase()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->assertEmpty($this->backendHandler->getMoves()->fetch_array());
    }

        // Issue #5
    public function testUndo_ThreeUndoTwoPlacedTilesOneMovedTile_MovesNotInState()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->move('0,0', '1,-1');

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->assertArrayNotHasKey('0,0', $this->stateHandler->getBoard());
    }

    // Issue #5
    public function testUndo_ThreeUndoTwoPlacedTilesOneMovedTile_MovesNotInDatabase()
    {
        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->move('0,0', '1,-1');

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->backendHandler->undo();

        $this->assertNotContains('0,0', $this->backendHandler->getMoves()->fetch_array());
    }
}
