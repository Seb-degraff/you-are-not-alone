<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use App\TelegramOutput;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;


class DeuxiemeChoixCommand extends UserCommand
{
    public function execute()
    {
        $message = $this->getMessage();

        $out = new TelegramOutput();
        $app = new App($out);

        $chat_id = $message->getChat()->getId();

        $player = $app->getOrCreatePlayer($message->getFrom()->getId());
        $game = $app->fetcher->getCurrentGameForPlayer($player);

        if (!$app->checkGameIsStarted($game)) {
            return;
        }

        if (!$app->checkPlayerIsInGame($player, $game)) {
            return;
        }

        $app->choose($player, 0);

        $app->printChat($chat_id, "Très bien...");
    }
}