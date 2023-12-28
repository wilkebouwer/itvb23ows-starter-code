<?php

session_start();

use Database\DatabaseHandler as DatabaseHandler;

$databaseHandler = new DatabaseHandler();
$database = $databaseHandler->getDatabase();

$stmt = $database->prepare('insert into moves
    (game_id, type, move_from, move_to, previous_id, state)
    values (?, "pass", null, null, ?, ?)');

$state = $databaseHandler->getState();

$stmt->bind_param('iis', $_SESSION['game_id'], $_SESSION['last_move'], $state);
$stmt->execute();
$_SESSION['last_move'] = $database->insert_id;
$_SESSION['player'] = 1 - $_SESSION['player'];

header('Location: ../index.php');
