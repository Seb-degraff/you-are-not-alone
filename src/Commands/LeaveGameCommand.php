<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use App\TelegramOutput;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

/**
 * User "/echo" command
 *
 * Simply echo the input back to the user.
 */
class LeaveGameCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'leave_game';
    /**
     * @var string
     */
    protected $description = 'Leave a game';
    /**
     * @var string
     */
    protected $usage = '';
    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execute method
     */
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
            $app->printChat($chatId, "?");
        }

        if ($game->current_turn != -1) {
            $app->printChat($chatId, $player->getDisplayName() . ", tu ne va quand meme pas quitter en pleine partie?");
            return;
        }

        $app->leaveGame($player, $game);
    }
}