<?php

use Fake\BackendHandlerMock as BackendHandlerMock;
Use Fake\AIConnectionHandlerMock as AIConnectionHandler;
use PHPUnit\Framework\TestCase;
use Board\BoardHandler as BoardHandler;
use State\StateHandler as StateHandler;

class AITest extends TestCase
{
    private BackendHandlerMock $backendHandler;
    private BoardHandler $boardHandler;
    private StateHandler $stateHandler;

    protected function setUp(): void
    {
        $this->backendHandler = new BackendHandlerMock();
        $this->stateHandler = $this->backendHandler->getStateHandler();

        $this->backendHandler->restart();
    }

    // Issue #11
    public function testMakeAIMove_MakeMoveFromEmptyBoard_MoveInState()
    {
        $aiConnectionHandler = new AIConnectionHandler(["play", "Q", "0,0"]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->makeAIMove();

        $this->assertArrayHasKey('0,0', $this->stateHandler->getBoard());
    }

    // Issue #11
    public function testMakeAIMove_MakeMoveFromEmptyBoard_MoveInDatabase()
    {
        $aiConnectionHandler = new AIConnectionHandler(["play", "Q", "0,0"]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->makeAIMove();

        $this->assertEquals('0,0', $this->backendHandler->getMoves()->fetch_array()[4]);
    }

    // Issue #11
    public function testPlay_MakeMoveAfterAIMoveUsingEmptyResults_MoveInState()
    {
        $aiConnectionHandler = new AIConnectionHandler(null);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->play('Q', '0,0');

        // Black (Fails)
        $this->boardHandler->makeAIMove();

        // Black
        $this->boardHandler->play('Q', '1,0');

        $this->assertArrayHasKey('1,0', $this->stateHandler->getBoard());
    }

    // Issue #11
    public function testPlay_MakeMoveAfterAIMoveUsingEmptyResults_MoveInDatabase()
    {
        $aiConnectionHandler = new AIConnectionHandler(null);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->play('Q', '0,0');

        // Black (Fails)
        $this->boardHandler->makeAIMove();

        // Black
        $this->boardHandler->play('Q', '1,0');

        $moves = $this->backendHandler->getMoves();

        $moves->fetch_array();

        $this->assertEquals('1,0', $moves->fetch_array()[4]);
    }

    // Issue #11
    public function testMakeAIMove_MakeSecondMove_MoveInState()
    {
        $aiConnectionHandler = new AIConnectionHandler(["play", "Q", "1,0"]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->makeAIMove();

        $this->assertArrayHasKey('1,0', $this->stateHandler->getBoard());
    }

    // Issue #11
    public function testMakeAIMove_MakeSecondMove_MoveInDatabase()
    {
        $aiConnectionHandler = new AIConnectionHandler(["play", "Q", "1,0"]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->makeAIMove();

        $moves = $this->backendHandler->getMoves();

        $moves->fetch_array();

        $this->assertEquals('1,0', $moves->fetch_array()[4]);
    }

    // Issue #11
    public function testMakeAIMove_MovePiece_MoveInState()
    {
        $aiConnectionHandler = new AIConnectionHandler(["move", "0,0", "0,1"]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->makeAIMove();

        $this->assertArrayHasKey('0,1', $this->stateHandler->getBoard());
    }

    // Issue #11
    public function testMakeAIMove_MovePiece_MoveInDatabase()
    {
        $aiConnectionHandler = new AIConnectionHandler(["move", "0,0", "0,1"]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

        // White
        $this->boardHandler->play('Q', '0,0');

        // Black
        $this->boardHandler->play('Q', '1,0');

        // White
        $this->boardHandler->makeAIMove();

        $moves = $this->backendHandler->getMoves();

        $moves->fetch_array();
        $moves->fetch_array();

        $this->assertEquals('0,1', $moves->fetch_array()[4]);
    }

    // Issue #11
    public function testMove_OtherPlayerMovesAfterAIPass_MoveInState() {
        $aiConnectionHandler = new AIConnectionHandler(["pass", null, null]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

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
        $this->boardHandler->makeAIMove();

        // White (Can make move because of black's successful pass)
        $this->boardHandler->move('-9,0', '13,0');

        $this->assertArrayHasKey('13,0', $this->stateHandler->getBoard());
    }

    // Issue #11
    public function testMove_OtherPlayerMovesAfterAIPass_MoveInDatabase() {
        $aiConnectionHandler = new AIConnectionHandler(["pass", null, null]);

        $this->boardHandler = new BoardHandler($this->backendHandler, $aiConnectionHandler);

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
        $this->boardHandler->makeAIMove();

        // White (Can make move because of black's successful pass)
        $this->boardHandler->move('-9,0', '13,0');

        $moves = $this->backendHandler->getMoves();

        // All moves that have been made minus 1
        for ($i = 0; $i < 24; $i++) {
            $moves->fetch_array();
        }

        $this->assertEquals('13,0', $moves->fetch_array()[4]);
    }
}
