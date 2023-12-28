<?php

session_start();

use Database\DatabaseHandler as DatabaseHandler;

$databaseHandler = new DatabaseHandler();
$database = $databaseHandler->getDatabase();

$stmt = $database->prepare('SELECT * FROM moves WHERE id = '.$_SESSION['last_move']);
$stmt->execute();
$result = $stmt->get_result()->fetch_array();
$_SESSION['last_move'] = $result[5];
$databaseHandler->setState($result[6]);
header('Location: ../index.php');
