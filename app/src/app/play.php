<?php

session_start();

include_once 'Util/util.php';

// TODO: Temporary
require './bootstrap.php';

use Database\DatabaseHandler as DatabaseHandler;

$piece = $_POST['piece'];
$to = $_POST['to'];

$player = $_SESSION['player'];
$board = $_SESSION['board'];
$hand = $_SESSION['hand'][$player];

if (!$hand[$piece]) {
    $_SESSION['error'] = "Player does not have tile";
} elseif (isset($board[$to])) {
    $_SESSION['error'] = 'Board position is not empty';
} elseif (count($board) && !hasNeighbour($to, $board)) {
    $_SESSION['error'] = "board position has no neighbour";
} elseif (array_sum($hand) < 11 && !neighboursAreSameColor($player, $to, $board)) {
    $_SESSION['error'] = "Board position has opposing neighbour";
} elseif (array_sum($hand) <= 8 && $hand['Q']) {
    $_SESSION['error'] = 'Must play queen bee';
} else {
    $_SESSION['board'][$to] = [[$_SESSION['player'], $piece]];
    $_SESSION['hand'][$player][$piece]--;
    $_SESSION['player'] = 1 - $_SESSION['player'];

    $databaseHandler = new DatabaseHandler();
    $database = $databaseHandler->getDatabase();

    $stmt = $database->prepare('insert into moves
        (game_id, type, move_from, move_to, previous_id, state)
        values (?, "play", ?, ?, ?, ?)');

    $state = $databaseHandler->getState();

    $stmt->bind_param('issis', $_SESSION['game_id'], $piece, $to, $_SESSION['last_move'], $state);
    $stmt->execute();
    $_SESSION['last_move'] = $database->insert_id;
}

header('Location: ../index.php');
