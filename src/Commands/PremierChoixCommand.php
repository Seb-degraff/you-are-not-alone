<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;


class PremierChoixCommand extends UserCommand
{
    public function execute()
    {
        $message = $this->getMessage();

        $app = new App($message);

        $chat_id = $message->getChat()->getId();

        if (!$app->checkGameIsStarted()) {
            $app->printChat($chat_id, "Le jeu n'a pas encore commencé. (/startGame)");
            return;
        }

        $currentPlayer = $app->player;

        if (!$currentPlayer) {
            $app->printChat($chat_id, "Vous ne faites pas partie du jeu pour le moment.");
            return;
        }

        $input = trim($message->getText(true));

        $app->printChat($chat_id, "Très bien...");

        $app->choose(1);
    }
}