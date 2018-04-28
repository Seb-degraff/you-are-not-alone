<?php

namespace App;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class Kernel
{
    /**
     * @var Kernel
     */
    public static $instance;
    public $storyContent;

    public $fetcher;
    public $telegram;

    public $pdo;

    public $config;

    public function __construct(array $config)
    {
        static::$instance = $this;

        $whoops = new \Whoops\Run();
        $errorHandler = new \Whoops\Handler\PrettyPageHandler();
        $errorHandler->setEditor("phpstorm");
        $whoops->pushHandler($errorHandler);
        $whoops->register();

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

            $this->telegram->addCommandsPath(__DIR__ . "/Commands/SystemCommands/");
            $this->telegram->addCommandsPath(__DIR__ . "/Commands/");

            //$messages = $response->getRawData()['result'];
        } catch (TelegramException $e) {
            // log telegram errors
            die ($e->getMessage());
        }
    }

    public function initDb($mysql_credentials)
    {
        $dsn     = 'mysql:host=' . $mysql_credentials['host'] . ';dbname=' . $mysql_credentials['database'];

        $options = [
            //\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $encoding
        ];

        $pdo = new \PDO($dsn, $mysql_credentials['user'], $mysql_credentials['password'], $options);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);

        return $pdo;
    }

    public function doTelegramWebHook()
    {
        //Request::sendMessage(['chat_id' => '350906840', 'text' => 'Ça marche'] );
        try {
            // Web hook
            $this->telegram->handle();
        } catch (TelegramException $e) {
            // log telegram errors
            die ($e->getMessage());
        }
    }

    public function doTelegramUpdates()
    {
        try {
            // Handle telegram getUpdates request
            $this->telegram->handleGetUpdates();
        } catch (TelegramException $e) {
            // log telegram errors
            die ($e->getMessage());
        }
    }
}