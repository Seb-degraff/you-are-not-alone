<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use App\Kernel;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class JoinGameCommand extends UserCommand
{
    const NAME = "/joinGame";

    public function execute()
    {
        $message = $this->getMessage();

        $app = new App($message);

        if (!$app->checkIsInGroupChat()) return;

//        if (!$app->checkHasGame()) return;

        if (!$app->joinGame($app->game, $message->getFrom()->getId())) return;
    }
}