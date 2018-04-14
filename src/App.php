<?php

namespace App;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class App
{
    /**
     * @var App
     */
    public static $instance;

    public $players;
    public $telegram;

    public $pdo;

    private $config;

    public function __construct(array $config)
    {
        static::$instance = $this;

        $this->config = $config;

        $bot_api_key  = '585514040:AAG3Kaug44Db4Or9KFbY_dkoAs_mfwe5TNU';
        $bot_username = 'youarenotalone';
        $mysql_credentials = $config['db_credentials'];

        try {
            // Create Telegram API object
            $this->telegram = new Telegram($bot_api_key, $bot_username);

            $this->pdo = $this->initDb($mysql_credentials);

            // Enable MySQL
            $this->telegram->enableExternalMySql($this->pdo);

            $this->players = new Players();

            $this->telegram->addCommandsPath(__DIR__ . "/Commands/SystemCommands/");
            $this->telegram->addCommandsPath(__DIR__ . "/Commands/");

            // Handle telegram getUpdates request
            $response = $this->telegram->handleGetUpdates();

//            print_r($response);

            //$messages = $response->getRawData()['result'];
        } catch (TelegramException $e) {
            // log telegram errors
            echo $e->getMessage();
        }
        $this->players->getAllPlayers();
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
}