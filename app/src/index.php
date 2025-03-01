<?php
    session_start();

    use Backend\BackendHandler as BackendHandler;
    use \AIConnection\AIConnectionHandler as AIConnectionHandler;
    use Board\BoardHandler as BoardHandler;

    require_once './vendor/autoload.php';

    $backendHandler = new BackendHandler();

    $boardHandler = new BoardHandler($backendHandler, new AIConnectionHandler());
    $stateHandler = $backendHandler->getStateHandler();

    $indexLocationHeader = "Location: ./index.php";

    // Handle 'Restart' button press and unset board (initial condition)
    if (array_key_exists('restart', $_POST) || $stateHandler->getBoard() == null) {
        $backendHandler->restart();
    }

    // It's only safe after restart to set board variable
    $board = $stateHandler->getBoard();
    $player = $stateHandler->getPlayer();

    // Handle 'Pass' button press
    if(array_key_exists('pass', $_POST)) {
        $boardHandler->pass();
        header($indexLocationHeader);
    }

    // Handle 'Restart' button press
    if(array_key_exists('restart', $_POST)) {
        $backendHandler->restart();
        header($indexLocationHeader);
    }

    // Handle 'Undo' button press
    if(array_key_exists('undo', $_POST)) {
        $backendHandler->undo();
        header($indexLocationHeader);
    }

    // Handle 'Play' button press
    if(array_key_exists('play', $_POST)) {
        $piece = $_POST['piece'];
        $to = $_POST['to'];

        $boardHandler->play($piece, $to);

        header($indexLocationHeader);
    }

    // Handle 'Move' button press
    if(array_key_exists('move', $_POST) && isset($_POST['from'])) {
        $from = $_POST['from'];
        $to = $_POST['to'];

        $boardHandler->move($from, $to);

        header($indexLocationHeader);
    }

    // Handle 'AI' button press
    if (array_key_exists('ai', $_POST)) {
        $boardHandler->makeAIMove();

        header($indexLocationHeader);
    }

    $hand = $stateHandler->getHand();
    $to = $boardHandler->getPossiblePositions();

    // Used later to print White and Black's hand
    function printHand($hand, $player) {
        foreach ($hand[$player] as $tile => $ct) {
        for ($i = 0; $i < $ct; $i++) {
            echo '<div class="tile player' . $player . '"><span>'.$tile."</span></div> ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Hive</title>
        <link rel="stylesheet" type="text/css" href="./css/default.css">
    </head>
    <body>
        <h1 class="gameStatus">
            <?php
                $whiteLost = $boardHandler->lostGame(0);
                $blackLost = $boardHandler->lostGame(1);

                if ($whiteLost && $blackLost) {
                    echo "Draw!";
                } else {
                    if ($whiteLost) {
                        echo "Black wins!";
                    } elseif ($blackLost) {
                        echo "White wins!";
                    }
                }
            ?>
        </h1>
        <div class="board">
            <?php
                $min_p = INF;
                $min_q = INF;

                // Get smallest X and Y position values of the board
                foreach ($board as $pos => $tile) {
                    $pq = explode(',', $pos);
                    if ($pq[0] < $min_p) {
                        $min_p = $pq[0];
                    }
                    if ($pq[1] < $min_q) {
                        $min_q = $pq[1];
                    }
                }

                // Print board
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
                printHand($hand, 0);
            ?>
        </div>
        <div class="hand">
            Black:
            <?php
                printHand($hand, 1);
            ?>
        </div>
        <div class="turn">
            Turn: <?php
                        if ($player == 0) {
                            echo "White";
                        } else {
                            echo "Black";
                        }
                  ?>
        </div>
        <form method="post">
            <label>
                <select name="piece">
                    <?php
                        // Add pieces in hand of current player as dropdown options
                        foreach ($boardHandler->getAvailableHandPieces() as $piece) {
                            echo "<option value=\"$piece\">$piece</option>";
                        }
                    ?>
                </select>
            </label>
            <label>
                <select name="to">
                    <?php
                        // Add possible play positions as dropdown options
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
                        // Add all player piece positions to dropdown options
                        foreach ($boardHandler->getPlayerPiecePositions() as $pos) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    ?>
                </select>
            </label>
            <label>
                <select name="to">
                    <?php
                        // Add possible move positions as dropdown options
                        foreach ($to as $pos) {
                            echo "<option value=\"$pos\">$pos</option>";
                        }
                    ?>
                </select>
            </label>
            <input type="submit" name="move" value="Move">
        </form>
        <form method="post">
            <input type="submit" name="ai" value="AI">
        </form>
        <form method="post">
            <input type="submit" name="pass" value="Pass">
        </form>
        <form method="post">
            <input type="submit" name="restart" value="Restart">
        </form>

        <strong>
            <?php
                // Print error
                if ($stateHandler->getError() !== null) {
                    echo $stateHandler->getError();
                }
            ?>
        </strong>
        <ol>
            <?php
                // Add list of all moves that have been done this game
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
