<?php

session_start();

// TODO: Temporary
require './bootstrap.php';

use Database\DatabaseHandler as DatabaseHandler;
use State\StateHandler as StateHandler;

$databaseHandler = new DatabaseHandler();
$database = $databaseHandler->getDatabase();

$stmt = $database->prepare('SELECT * FROM moves WHERE id = ?');

$stmt->bind_param("s", $_SESSION['last_move']);
$stmt->execute();

$result = $stmt->get_result()->fetch_array();
$_SESSION['last_move'] = $result[5];

$stateHandler = new StateHandler();

$stateHandler->setStateFromSerialized($result[6]);
header('Location: ../index.php');
