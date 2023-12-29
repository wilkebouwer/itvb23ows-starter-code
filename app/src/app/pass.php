<?php

session_start();

use Backend\BackendHandler as BackendHandler;

$backendHandler = new BackendHandler();

$backendHandler->setMove(null, null);

header('Location: ../index.php');
