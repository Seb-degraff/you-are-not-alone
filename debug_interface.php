<?php

require __DIR__ . "/vendor/autoload.php";

$config = include('config.php');

$kernel = new \App\Kernel($config);

$outputInterface = new \App\BufferOutput();

$app = new \App\App($outputInterface);

switch ($_POST['action'] ?? '') {
    case 'add_player':
    {
        $app->fetcher->newPlayer($_POST['new_player_name']);
    } break;

    case 'start_game':
    {
        $game = $app->fetcher->findGameById($_POST['game_id']);
        $app->startGame($game);
    } break;

    case 'end_game':
    {
        $game = $app->fetcher->findGameById($_POST['game_id']);
        $app->endGame($game);
    } break;
 
    case 'join_game':
    {
        $game = $app->fetcher->findGameById($_POST['game_id']);
        $player = $app->fetcher->findPlayerById($_POST['player_id']);
        $app->joinGame($game, $player);
    } break;

    case 'choose_action':
    {
        $player = $app->fetcher->findPlayerById($_POST['player_id']);
        $choice = (int) $_POST['choice'];
        $app->choose($player, $choice);
    } break;

    default: {}
}

$allPlayers = $app->fetcher->findAllPlayers();
$allGame = $app->fetcher->findAllGames();

function dumpObject($obj)
{
    $rows = get_object_vars($obj);
    drawTable($rows);
}

function drawTable($rows)
{
    ?>
    <table style="display: inline-block; width: 19%; text-align: left; margin-bottom: 30px">
        <?php foreach ($rows as $name => $value): ?>
            <tr><th style="width: 50%"><?= $name ?></th><td style="width: 50%"><?= $value ?></td></tr>
        <?php endforeach; ?>
    </table>
    <?php
}

?>

<html>
<body>
    <div>
        <h5>output</h5>
        <?php foreach ($outputInterface->messages as $message): ?>
            <pre><?= $message ?></pre>
        <?php endforeach; ?>
    </div>
    <h5>add player</h5>
    <form action="debug_interface.php" method="post">
        <input type="hidden" name="action" value="add_player">
        <input type="text" name="new_player_name">
        <button type="submit">Add</button>
    </form>

    <h5>games:</h5>
    <?php foreach ($allGame as $game): ?>
        <?php dumpObject($game); ?>

        <!-- Start Game -->
        <div style="display: inline-block">
            <form action="debug_interface.php" method="post">
                <input type="hidden" name="action" value="start_game">
                <input type="hidden" name="game_id" value="<?= $game->id ?>">
                <button type="submit">Start Game</button>
            </form>
        </div>

        <!-- End Game -->
        <div style="display: inline-block">
            <form action="debug_interface.php" method="post">
                <input type="hidden" name="action" value="end_game">
                <input type="hidden" name="game_id" value="<?= $game->id ?>">
                <button type="submit">End game</button>
            </form>
        </div>

    <?php endforeach; ?>
    <h5>players:</h5>
    <?php foreach ($allPlayers as $player): ?>
        <?php //dumpObject($player); ?>
        <?php
        $rows = [];
        $rows['name'] = $player->getDisplayName();
        $rows['game id'] = $player->game_id;
        $rows['is dead'] = $player->is_dead ? "yes" : "no";
        $rows['has done vision'] = $player->has_done_vision ? "yes" : "no";
        $rows['choice'] = $player->action_chosen === null ? "pending" : ($player->action_chosen === "1" ? "action 1" : "action 2");
        drawTable($rows);
        ?>


        <!-- Join Game -->
        <div style="display: inline-block">
            <form action="debug_interface.php" method="post">
                <input type="hidden" name="action" value="join_game">
                <input type="hidden" name="player_id" value="<?= $player->participant_id ?>">
                <input type="number" name="game_id">
                <button type="submit">Join Game</button>
            </form>
        </div>
        <!-- Join Game -->
        <div style="display: inline-block">
            <form action="debug_interface.php" method="post">
                <input type="hidden" name="action" value="choose_action">
                <input type="hidden" name="player_id" value="<?= $player->participant_id ?>">
                <select name="choice">
                    <option value="1">1</option>
                    <option value="0">2</option>
                </select>
                <button type="submit">Choose action</button>
            </form>
        </div>
    <?php endforeach; ?>
</body>
</html>
