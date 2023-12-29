<?php

session_start();

use Backend\BackendHandler as BackendHandler;

include_once 'Util/util.php';

$from = $_POST['from'];
$to = $_POST['to'];

$player = $_SESSION['player'];
$board = $_SESSION['board'];
$hand = $_SESSION['hand'][$player];
unset($_SESSION['error']);

if (!isset($board[$from])) {
    $_SESSION['error'] = 'Board position is empty';
} elseif ($board[$from][count($board[$from])-1][0] != $player) {
    $_SESSION['error'] = "Tile is not owned by player";
} elseif ($hand['Q']) {
    $_SESSION['error'] = "Queen bee is not played";
} else {
    $tile = array_pop($board[$from]);
    if (!hasNeighbour($to, $board)) {
        $_SESSION['error'] = "Move would split hive";
    } else {
        $all = array_keys($board);
        $queue = [array_shift($all)];
        while ($queue) {
            $next = explode(',', array_shift($queue));
            foreach ($GLOBALS['OFFSETS'] as $pq) {
                list($p, $q) = $pq;
                $p += $next[0];
                $q += $next[1];
                if (in_array("$p,$q", $all)) {
                    $queue[] = "$p,$q";
                    $all = array_diff($all, ["$p,$q"]);
                }
            }
        }
        if ($all) {
            $_SESSION['error'] = "Move would split hive";
        } else {
            if ($from == $to) {
                $_SESSION['error'] = 'Tile must move';
            } elseif (isset($board[$to]) && $tile[1] != "B") {
                $_SESSION['error'] = 'Tile not empty';
            } elseif ($tile[1] == "Q" || $tile[1] == "B") {
                if (!slide($board, $from, $to)) {
                    $_SESSION['error'] = 'Tile must slide';
                }
            }
        }
    }
    if (isset($_SESSION['error'])) {
        $board[$from] = [$tile];
    } else {
        if (isset($board[$to])) {
            $board[$to] = [$tile];
        } else {
            $board[$to] = [$tile];
        }

        $backendHandler = new BackendHandler();

        $backendHandler->addMove($from, $to);
    }
    $_SESSION['board'] = $board;
}

header('Location: ../index.php');
