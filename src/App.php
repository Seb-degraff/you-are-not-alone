<?php

namespace App;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class App
{
    /**
     * @var App
     */
    public static $instance;
    public $storyContent;

    public $fetcher;
    public $telegram;

    public $pdo;

    private $config;


    /**
     * @var Game
     */
    private $current_game;

    public function __construct(array $config, $isWebHook)
    {
        static::$instance = $this;

        $this->storyContent = include (__DIR__ . "/../resources/story_content.php");

        $this->config = $config;

        $bot_api_key  = $config['bot_api_key'];
        $bot_username = $config['bot_username'];
        $mysql_credentials = $config['db_credentials'];

        try {
            // Create Telegram API object
            $this->telegram = new Telegram($bot_api_key, $bot_username);

            $this->pdo = $this->initDb($mysql_credentials);

            // Enable MySQL
            $this->telegram->enableExternalMySql($this->pdo);

            $this->fetcher = new Fetcher();

            $this->telegram->addCommandsPath(__DIR__ . "/Commands/SystemCommands/");
            $this->telegram->addCommandsPath(__DIR__ . "/Commands/");

            if ($isWebHook) {
                // Web hook
                //Request::sendMessage(['chat_id' => '350906840', 'text' => 'Ça marche'] );
                $this->telegram->handle();
            }
            else {
                // Handle telegram getUpdates request
                $this->telegram->handleGetUpdates();
            }

            //$messages = $response->getRawData()['result'];
        } catch (TelegramException $e) {
            // log telegram errors
            echo $e->getMessage();
        }
        $this->fetcher->getAllPlayersData();
    }

    private function initDb($mysql_credentials)
    {
        $dsn     = 'mysql:host=' . $mysql_credentials['host'] . ';dbname=' . $mysql_credentials['database'];

        $options = [
            //\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $encoding
        ];

        $pdo = new \PDO($dsn, $mysql_credentials['user'], $mysql_credentials['password'], $options);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);

        return $pdo;
    }

    public function nextTurn()
    {
        $this->current_game = $this->fetcher->getCurrentGame();

        $players = $this->fetcher->getAllPlayers();

        $notDeadPlayers = $this->getNotDeadPlayers($players);

        if (count($notDeadPlayers) == 0) {
            $this->printGameChat("Dommage, personne n’a réussi à survivre à cette épreuve ! Fin de la partie.");
            $this->endGame();
            return;
        }

        if (count($notDeadPlayers) == 1) {
            $this->sendImage($this->fetcher->getCurrentGame()->chat_id, 'http://gold.arrache.ch/public/images/butin.png');
//            $this->printGameChat($notDeadPlayers[0]->getDisplayName() . " gagne la partie et le trésor, félicitation!!!");
            $this->printGameChat('*' . $notDeadPlayers[0]->getDisplayName() . ":* Bien joué ! Vous êtes le dernier survivant ! prenez le butin, et faites vous plaisir !");
            $this->endGame();
            return;
        }

        $currentTurn = $this->current_game->current_turn;
        $this->fetcher->setGameCurrentTurn($this->current_game, ++$currentTurn);

        if ($currentTurn >= 5) {
            $this->sendImage($this->fetcher->getCurrentGame()->chat_id, 'http://gold.arrache.ch/public/images/butin.png');
            $this->printGameChat("Bravo ! vous êtes venus à bout des épreuves ensemble ! vous pouvez vous partager le butin !");
            $this->endGame();
        }

        sleep(2);

        $currentStory = $this->getCurrentScenario();

        $this->printGameChat(strtoupper('*' . $currentStory['title'] . '*'), true);

        $this->sendImage($this->fetcher->getCurrentGame()->chat_id, $currentStory['image']);

        $this->printGameChat($currentStory['story']);

        sleep(2);

        $this->printGameChat('Deux choix se présentent à vous:');
        sleep(1);
        $this->printGameChat("*Choix 1:* " . $currentStory['choice0'], true);
        sleep(1);
        $this->printGameChat("*Choix 2:* " . $currentStory['choice1'], true);

        sleep(1);

        if (count($notDeadPlayers) == 2) {
            $this->printGameChat("Vous n'êtes plus que deux joueurs, vous ne pouvez plus faire de voyance. Seuls les fantômes ou la chance pourront vous venir en aide.");
            return;
        } else {
            $this->printGameChat("Mais avant que vous preniez votre décision, je peux effectuer pour vous une vision. Parlez-moi en privé (/vision joueur)");
        }

        sleep(2);

        $damnedOneParticipantId = $notDeadPlayers[rand(0, count($notDeadPlayers) - 1)]->participant_id;

        $sql = "UPDATE games SET damned_one_participant_id = $damnedOneParticipantId";
        $statement2 = $this->pdo->query($sql);
        $statement2->execute();


        foreach ($players as $key => $player) {
            $userId = $player->user_id;

            // reset player state
            $this->fetcher->playerSetActionChosen($player, null);
            $this->fetcher->playerSetHasDoneVision($player, false);

            $visionOptions = [];

            foreach ($notDeadPlayers as $notDeadPlayer) {
                if ($notDeadPlayer->user_id != $player->user_id) {
                    $visionOptions[] = $notDeadPlayer->getDisplayName();
                }
            }

            $reponse = $this->printChat($userId, 'Choisissez une personne dont vous voulez voir le futur (/vision + nom). ' . join($visionOptions, ', '));

//                if ($key == $chosenOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le chosen one']);
//                } else if ($key == $damnedOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le damned one']);
//                }
        }
    }

    public function endGame()
    {
        sleep(2);
        $this->printGameChat("Voulez-vous recommencer ? /startGame");

        $st = $this->pdo->query("TRUNCATE games");
        $st->execute();
    }

    public function removePlayer(Player $player)
    {
        $this->fetcher->removeGameParticipant($player);
    }

    public function getNotDeadPlayers($players)
    {
        $notDeadPlayers = [];

        foreach ($players as $player) {
            if ($player->is_dead == 0) {
                $notDeadPlayers[] = $player;
            }
        }

        return $notDeadPlayers;
    }

    public function printGameChat($text = null, $markdown = false)
    {
        return $this->printChat($this->fetcher->getCurrentGame()->chat_id, $text, $markdown);
    }

    public function printChat($chat_id, $text, $markdown = false)
    {
        $data['chat_id'] = $chat_id;
        $data['text'] = $text;
        if ($markdown)
            $data['parse_mode'] = 'Markdown';

        $response = Request::sendMessage($data);

        if (!$response->isOk()) {
            echo "Error when sending $text to $chat_id." . PHP_EOL;
            print_r($response);
        }

        return $response;
    }

    public function sendImage($chat_id, $imageUrl)
    {
        $data['chat_id'] = $chat_id;
        $data['photo'] = $imageUrl;

        $response = Request::sendPhoto($data);

        if (!$response->isOk()) {
            echo "Error when sending image $imageUrl to $chat_id." . PHP_EOL;
            print_r($response);
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function checkGameIsStarted()
    {
        return $this->fetcher->getCurrentGame() != null;
    }

    public function getCurrentScenario()
    {
        $game = $this->fetcher->getCurrentGame();

        return $this->storyContent['scenarios'][$game->current_turn];
    }
}