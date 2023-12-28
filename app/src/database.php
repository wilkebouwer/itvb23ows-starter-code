<?php

function getState() {
    return serialize(
        [
            $_SESSION['hand'],
            $_SESSION['board'],
            $_SESSION['player']
        ]
    );
}

function setState($state) {
    list($a, $b, $c) = unserialize($state);

    $_SESSION['hand'] = $a;
    $_SESSION['board'] = $b;
    $_SESSION['player'] = $c;
}

return new mysqli(
    'mysql',
    'root',
    getenv('MYSQL_ROOT_PASSWORD'),
    getenv('MYSQL_DATABASE')
);
