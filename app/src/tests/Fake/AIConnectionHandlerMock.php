<?php

namespace Fake;

use AIConnection\AIConnectionHandler;

class AIConnectionHandlerMock extends AIConnectionHandler
{
    private $result;

    // Set result that will be received when calling getAIMoveArray
    public function __construct($result)
    {
        $this->result = $result;
    }

    // Replace server call with a predefined result
    public function getAIMoveArray($moveNumber, $hand, $board)
    {
        return $this->result;
    }
}