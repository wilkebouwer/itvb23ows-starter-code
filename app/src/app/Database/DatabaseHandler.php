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

    // TODO: Place in different file (StateHandler)?
    public function getState(): string
    {
        return serialize(
            [
                $_SESSION['hand'],
                $_SESSION['board'],
                $_SESSION['player']
            ]
        );
    }

    // TODO: Place in different file (StateHandler)?
    public function setState($state)
    {
        list($a, $b, $c) = unserialize($state);

        $_SESSION['hand'] = $a;
        $_SESSION['board'] = $b;
        $_SESSION['player'] = $c;
    }
}
