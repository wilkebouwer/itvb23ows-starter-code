<?php

namespace Database;

use mysqli;

// TODO: Run as singleton maybe?
class DatabaseHandler
{
    private mysqli $database;

    public function __construct()
    {
        $this->database = new mysqli(
            'mysql',
            'root',
            getenv('MYSQL_ROOT_PASSWORD'),
            getenv('MYSQL_DATABASE')
        );
    }

    public function getDatabase(): mysqli
    {
        return $this->database;
    }

    public function setMove($types, $gameId, $from, $to, $move, $state)
    {
        $stmt = $this->database->prepare('INSERT INTO moves
            (game_id, type, move_from, move_to, previous_id, state)
            VALUES (?, "move", ?, ?, ?, ?)');

        $stmt->bind_param($types, $gameId, $from, $to, $move, $state);

        $stmt->execute();
    }

    public function addNewGame() {
        $this->database->prepare('INSERT INTO games VALUES ()')->execute();
    }

    public function getInsertID()
    {
        return $this->database->insert_id;
    }
}
