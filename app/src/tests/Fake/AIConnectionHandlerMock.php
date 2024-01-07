<?php

namespace Fake;

use AIConnection\AIConnectionHandler;

class AIConnectionHandlerMock extends AIConnectionHandler
{
    private $results;

    // Set results that will be received when calling getResults
    public function __construct($results)
    {
        $this->results = $results;
    }

    // Replace server call with a predefined result
    public function getResults($moveNumber, $hand, $board)
    {
        return $this->results;
    }
}
