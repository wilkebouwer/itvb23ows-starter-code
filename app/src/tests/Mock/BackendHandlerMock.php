<?php

namespace Mock;

use Backend\BackendHandler;
use State\StateHandler;

class BackendHandlerMock extends BackendHandler {

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