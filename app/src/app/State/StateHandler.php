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

    public function decreasePiece($piece)
    {
        $_SESSION['hand'][$_SESSION['player']][$piece]--;
    }

    public function setBoardPiece($to, $piece)
    {
        $_SESSION['board'][$to] = [[$_SESSION['player'], $piece]];
    }

    public function setLastMove($id) {
        $_SESSION['last_move'] = $id;
    }

    public function getBoard() {
        return $_SESSION['board'];
    }

    public function setBoard($board) {
        $_SESSION['board'] = $board;
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

    public function getError() {
        return $_SESSION['error'];
    }

    public function setError($error) {
        $_SESSION['error'] = $error;
    }

    public function getHand() {
        return $_SESSION['hand'];
    }

    public function setHand($hand) {
        $_SESSION['hand'] = $hand;
    }
}