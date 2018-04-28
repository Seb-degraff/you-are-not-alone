<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use App\TelegramOutput;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;


class PremierChoixCommand extends UserCommand
{
    public function execute()
    {
        $message = $this->getMessage();

        $out = new TelegramOutput();
        $app = new App($out);

        $chat_id = $message->getChat()->getId();

        $player = $app->getOrCreatePlayer($message->getFrom()->getId());
        $game = $app->fetcher->getCurrentGameForPlayer($player);

        if (!$app->checkGameIsStarted($game, $chat_id)) {
            return;
        }

        if (!$app->checkPlayerIsInGame($player, $game)) {
            return;
        }

        $app->choose($player, 1);

        $app->printChat($chat_id, "Très bien...");
    }
}