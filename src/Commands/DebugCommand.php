<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class DebugCommand extends UserCommand
{
    public function execute()
    {
        $message = $this->getMessage();
        $user = $message->getFrom();

        $chat_id = $message->getChat()->getId();

        $input = trim($message->getText(true));

        $app = App::$instance;

        $players = $app->fetcher->getAllPlayers();


        $output = "*player states*:" . PHP_EOL;

        foreach ($players as $player)
        {
            $output .= print_r($player, true);
            $output .= PHP_EOL;
        }


        $output .= "*game state*:" . PHP_EOL;
        $currentGame = $app->fetcher->getCurrentGame();

        if ($currentGame) {
            $output .= print_r($currentGame, true);
        } else {
            $output .= "No game currently in db";
        }

        $app->printChat($chat_id, $output, true);
    }
}