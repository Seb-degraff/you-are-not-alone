<?php

require __DIR__ . "/vendor/autoload.php";

$config = include('config.php');

$bot_api_key  = $config['bot_api_key'];
$bot_username = $config['bot_username'];
$mysql_credentials = $config['db_credentials'];
$hook_url = $config['web_hook_url'];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

    // Set webhook
    $result = $telegram->setWebhook($hook_url);
//    if ($result->isOk()) {
        echo $result->getDescription();
//    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
     echo $e->getMessage();
}
