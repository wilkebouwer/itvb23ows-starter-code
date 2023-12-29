<?php

namespace State;

class StateHandler
{
    public function getSerializedState(): string
    {
        return serialize(
            [
                $_SESSION['hand'],
                $_SESSION['board'],
                $_SESSION['player']
            ]
        );
    }

    public function setStateFromSerialized($state)
    {
        list($a, $b, $c) = unserialize($state);

        $_SESSION['hand'] = $a;
        $_SESSION['board'] = $b;
        $_SESSION['player'] = $c;
    }

    public function restart() {
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
    }

    public function switchPlayer() {
        $_SESSION['player'] = 1 - $_SESSION['player'];
    }

    public function setLastMove($id) {
        $_SESSION['last_move'] = $id;
    }

    public function getGameID() {
        return $_SESSION['game_id'];
    }

    public function setGameID($gameID) {
        $_SESSION['game_id'] = $gameID;
    }

    public function getLastMove() {
        return $_SESSION['last_move'];
    }

    public function getPlayer() {
        return $_SESSION['player'];
    }

    public function setPlayer($player) {
        $_SESSION['player'] = $player;
    }
}