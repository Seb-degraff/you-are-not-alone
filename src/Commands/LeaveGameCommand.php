<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
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
        $user = $message->getFrom();

        $chat_id = $message->getChat()->getId();

        $app = App::$instance;

        $game = $app->fetcher->getCurrentGame();

        $player = $app->fetcher->getPlayerByTelegramId($user->getId());

        if ($game != null) {
            $app->printChat($chat_id, $player->getDisplayName() . ", tu ne va quand meme pas quitter en pleine partie?");
            return;
        }

        $app->removePlayer($player);

        $app->printChat($chat_id, $player->getDisplayName() . " a quitté la partie. Adieu 👋");
    }
}