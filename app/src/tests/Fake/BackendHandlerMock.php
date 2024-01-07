<?php

namespace Fake;

use Backend\BackendHandler;
use State\StateHandler;

class BackendHandlerMock extends BackendHandler {

    public function __construct()
    {
        $this->databaseHandler = new DatabaseHandlerMock();
        $this->stateHandler = new StateHandler();
    }
}