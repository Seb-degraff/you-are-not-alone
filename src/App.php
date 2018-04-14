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

    public function newTurn()
    {
        $this->current_game = $this->fetcher->getCurrentGame();

        $players = $this->fetcher->getAllPlayers();

        $notDeadPlayers = [];
        $playerNamesList = [];

        foreach ($players as $player) {
            if ($player->is_dead == 0) {
                $notDeadPlayers[] = $player;
                $playerNamesList[] = $player->getDisplayName();
            }
        }

        if (count($notDeadPlayers) == 0) {
            $this->printChat("Tout le monde est mort, fin de la partie.");
            return;
        }

        if (count($notDeadPlayers) == 1) {
            $this->printChat($notDeadPlayers[0]->getDisplayName() . " gagne la partie et le trésor, félicitation!!! bravo!");
            return;
        }

        $damnedOneParticipantId = $notDeadPlayers[rand(0, count($notDeadPlayers) - 1)]->participant_id;

        $sql = "UPDATE games SET damned_one_participant_id = $damnedOneParticipantId";
        $statement2 = $this->pdo->query($sql);
        $statement2->execute();


        foreach ($players as $key => $player) {
            $userId = $player->user_id;

            $this->fetcher->playerSetActionChosen($player, null);

            Request::sendMessage(['chat_id' => $userId, 'text' => 'Choisissez une personne dont vous voulez voir le futur. ' . join($playerNamesList, ', ') ] );

//                if ($key == $chosenOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le chosen one']);
//                } else if ($key == $damnedOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le damned one']);
//                }
        }
    }

    function printChat($text)
    {
        Request::sendMessage(['chat_id' => $this->current_game->chat_id, 'text' => $text]);
    }
}