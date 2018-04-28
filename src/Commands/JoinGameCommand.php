<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use App\Kernel;
use App\TelegramOutput;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class JoinGameCommand extends UserCommand
{
    const NAME = "/joinGame";

    public function execute()
    {
        $message = $this->getMessage();

        $telegramUserId = $message->getFrom()->getId();
        $chatId = $message->getChat()->getId();

        $telegramOutput = new TelegramOutput();
        $app = new App($telegramOutput);

        if (!$app->checkIsGroupChat($chatId)) return;
//        if (!$app->checkHasGame()) return;

        $player = $app->getOrCreatePlayer($telegramUserId);

        $game = $app->fetcher->findGameByGroupChatId($chatId);

        if (!$game) {
            $game = $app->fetcher->createGame($chatId);
        }

        $app->joinGame($game, $player);

        //if (!$app->joinGame($app->game, $message->getFrom()->getId())) return;
    }
}