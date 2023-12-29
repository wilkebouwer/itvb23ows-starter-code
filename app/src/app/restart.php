<?php

session_start();

// TODO: Temporary
require './bootstrap.php';

use Database\DatabaseHandler as DatabaseHandler;

$_SESSION['board'] = [];
$_SESSION['hand'] =
    [
        0 =>
        [
            "Q" => 1,
            "B" => 2,
            "S" => 2,
            "A" => 3,
            "G" => 3
        ],
        1 =>
        [
            "Q" => 1,
            "B" => 2,
            "S" => 2,
            "A" => 3,
            "G" => 3
        ]
    ];
$_SESSION['player'] = 0;

$databaseHandler = new DatabaseHandler();
$database = $databaseHandler->getDatabase();

$database->prepare('INSERT INTO games VALUES ()')->execute();
$_SESSION['game_id'] = $database->insert_id;

header('Location: ../index.php');
