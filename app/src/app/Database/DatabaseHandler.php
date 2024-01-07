<?php

namespace Database;

use mysqli;

class DatabaseHandler
{
    private mysqli $database;

    public function __construct()
    {
        $this->database = new mysqli(
            'mysqlserver',
            'root',
            getenv('MYSQL_ROOT_PASSWORD'),
            getenv('MYSQL_DATABASE')
        );
    }

    public function addMove($types, $gameId, $from, $to, $move, $state)
    {
        $stmt = $this->database->prepare('INSERT INTO moves
            (game_id, type, move_from, move_to, previous_id, state)
            VALUES (?, "move", ?, ?, ?, ?)');

        $stmt->bind_param($types, $gameId, $from, $to, $move, $state);

        $stmt->execute();
    }

    public function deleteMove($id) {
        $stmt = $this->database->prepare('DELETE FROM moves WHERE id = ?');

        $stmt->bind_param("s", $id);
        $stmt->execute();
    }

    public function deleteMoves($gameID) {
        $stmt = $this->database->prepare('DELETE FROM moves WHERE game_id = ?');

        $stmt->bind_param("s", $gameID);
        $stmt->execute();
    }

    public function getMoves($gameID) {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE game_id = ?');

        $stmt->bind_param('s', $gameID);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function getMove($id) {
        $stmt = $this->database->prepare('SELECT * FROM moves WHERE id = ?');

        $stmt->bind_param("s", $id);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function addNewGame() {
        $this->database->prepare('INSERT INTO games VALUES ()')->execute();
    }

    public function getInsertID()
    {
        return $this->database->insert_id;
    }
}
