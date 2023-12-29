<?php
    session_start();

    use Backend\BackendHandler as BackendHandler;
    use Board\BoardHandler as BoardHandler;

    require './app/bootstrap.php';

    $backendHandler = new BackendHandler();

    $boardHandler = new BoardHandler($backendHandler);
    $stateHandler = $backendHandler->getStateHandler();

    // Handle 'Pass' button press
    if(array_key_exists('pass', $_POST)) {
        $backendHandler->addMove(null, null);
        header('Location: ./index.php');
    }

    // Handle 'Restart' button press
    if(array_key_exists('restart', $_POST)) {
        $backendHandler->restart();
        header('Location: ./index.php');
    }

    // Handle 'Undo' button press
    if(array_key_exists('undo', $_POST)) {
        $backendHandler->undo();
        header('Location: ./index.php');
    }

    if (!isset($_SESSION['board'])) {
        $backendHandler->restart();
        header('Location: ./index.php');
        exit(0);
    }

    $board = $stateHandler->getBoard();
    $player = $stateHandler->getPlayer();

    // Handle 'Play' button press
    if(array_key_exists('play', $_POST)) {
        $piece = $_POST['piece'];
        $to = $_POST['to'];

        $boardHandler->play($board, $player, $piece, $to);

        header('Location: ./index.php');
    }

    // Handle 'Move' button press
    if(array_key_exists('move', $_POST)) {
        $from = $_POST['from'];
        $to = $_POST['to'];

        $boardHandler->move($board, $player, $from, $to);

        header('Location: ./index.php');
    }

    $hand = $stateHandler->getHand();

    $to = [];
    foreach ($boardHandler->getOffsets() as $pq) {
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
        <form method="post">
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
            <input type="submit" name="play" value="Play">
        </form>
        <form method="post">
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
            <input type="submit" name="move" value="Move">
        </form>
        <form method="post">
            <input type="submit" name="pass" value="Pass">
        </form>
        <form method="post">
            <input type="submit" name="restart" value="Restart">
        </form>

        <strong><?php if ($stateHandler->getError() !== null) { echo $stateHandler->getError(); $stateHandler->setError(null); } ?></strong>
        <ol>
            <?php
                $moves = $backendHandler->getMoves();
                while ($row = $moves->fetch_array()) {
                    echo '<li>'.$row[2].' '.$row[3].' '.$row[4].'</li>';
                }
            ?>
        </ol>
        <form method="post">
            <input type="submit" name="undo" value="Undo">
        </form>
    </body>
</html>
