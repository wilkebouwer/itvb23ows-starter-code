<?php
    session_start();

    require './app/bootstrap.php';

    include_once 'app/Util/util.php';

    if (!isset($_SESSION['board'])) {
        header('Location: ./app/restart.php');
        exit(0);
    }
    $board = $_SESSION['board'];
    $player = $_SESSION['player'];
    $hand = $_SESSION['hand'];

    $to = [];
    foreach ($GLOBALS['OFFSETS'] as $pq) {
        foreach (array_keys($board) as $pos) {
            $pq2 = explode(',', $pos);
            $to[] = ($pq[0] + $pq2[0]).','.($pq[1] + $pq2[1]);
        }
    }
    $to = array_unique($to);
    if (!count($to)) {
        $to[] = '0,0';
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Hive</title>
        <style>
            div.board {
                width: 60%;
                height: 100%;
                min-height: 500px;
                float: left;
                overflow: scroll;
                position: relative;
            }

            div.board div.tile {
                position: absolute;
            }

            div.tile {
                display: inline-block;
                width: 4em;
                height: 4em;
                border: 1px solid black;
                box-sizing: border-box;
                font-size: 50%;
                padding: 2px;
            }

            div.tile span {
                display: block;
                width: 100%;
                text-align: center;
                font-size: 200%;
            }

            div.player0 {
                color: black;
                background: white;
            }

            div.player1 {
                color: white;
                background: black
            }

            div.stacked {
                border-width: 3px;
                border-color: red;
                padding: 0;
            }
        </style>
    </head>
    <body>
        <div class="board">
            <?php
                $min_p = 1000;
                $min_q = 1000;
                foreach ($board as $pos => $tile) {
                    $pq = explode(',', $pos);
                    if ($pq[0] < $min_p) {
                        $min_p = $pq[0];
                    }
                    if ($pq[1] < $min_q) {
                        $min_q = $pq[1];
                    }
                }
                foreach (array_filter($board) as $pos => $tile) {
                    $pq = explode(',', $pos);
                    $h = count($tile);
                    echo '<div class="tile player';
                    echo $tile[$h-1][0];
                    if ($h > 1) {
                        echo ' stacked';
                    }
                    echo '" style="left: ';
                    echo ($pq[0] - $min_p) * 4 + ($pq[1] - $min_q) * 2;
                    echo 'em; top: ';
                    echo ($pq[1] - $min_q) * 4;
                    echo "em;\">($pq[0],$pq[1])<span>";
                    echo $tile[$h-1][1];
                    echo '</span></div>';
                }
            ?>
        </div>
        <div class="hand">
            White:
            <?php
                foreach ($hand[0] as $tile => $ct) {
                    for ($i = 0; $i < $ct; $i++) {
                        echo '<div class="tile player0"><span>'.$tile."</span></div> ";
                    }
                }
            ?>
        </div>
        <div class="hand">
            Black:
            <?php
            foreach ($hand[1] as $tile => $ct) {
                for ($i = 0; $i < $ct; $i++) {
                    echo '<div class="tile player1"><span>'.$tile."</span></div> ";
                }
            }
            ?>
        </div>
        <div class="turn">
            Turn: <?php if ($player == 0) { echo "White"; } else { echo "Black"; } ?>
        </div>
        <form method="post" action="app/play.php">
            <label>
                <select name="piece">
                    <?php
                        foreach ($hand[$player] as $tile => $ct) {
                            echo "<option value=\"$tile\">$tile</option>";
                        }
                    ?>
                </select>
            </label>
            <label>
                <select name="to">
                    <?php
                        foreach ($to as $pos) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    ?>
                </select>
            </label>
            <input type="submit" value="Play">
        </form>
        <form method="post" action="app/move.php">
            <label>
                <select name="from">
                    <?php
                        foreach (array_keys($board) as $pos) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    ?>
                </select>
            </label>
            <label>
                <select name="to">
                    <?php
                        foreach ($to as $pos) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    ?>
                </select>
            </label>
            <input type="submit" value="Move">
        </form>
        <form method="post" action="app/pass.php">
            <input type="submit" value="Pass">
        </form>
        <form method="post" action="app/restart.php">
            <input type="submit" value="Restart">
        </form>
        <strong><?php if (isset($_SESSION['error'])) { echo $_SESSION['error']; unset($_SESSION['error']); } ?></strong>
        <ol>
            <?php

            use Database\DatabaseHandler as DatabaseHandler;

            $databaseHandler = new DatabaseHandler();
                $database = $databaseHandler->getDatabase();

                // TODO: Unsafe SQL
                $stmt = $database->prepare('SELECT * FROM moves WHERE game_id = '.$_SESSION['game_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_array()) {
                    echo '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
                }
            ?>
        </ol>
        <form method="post" action="app/undo.php">
            <input type="submit" value="Undo">
        </form>
    </body>
</html>
