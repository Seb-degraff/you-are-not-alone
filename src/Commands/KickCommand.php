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
class KickCommand extends UserCommand
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

//        $message->getChat()

        $chat_id = $message->getChat()->getId();

        $input = trim($message->getText(true));

        $app = new App($message);

        $players = $app->fetcher->getAllPlayers();

        $text = "je n'ai pas compris";

        foreach ($players as $player)
        {
            if (strtolower($player->getDisplayName()) == strtolower($input)) {
                $app->removePlayer($player);
                $text = $player->getDisplayName() . " à été dégagé par un admin";
            }
        }

        $app->printChat($chat_id,  $text); //TODO: use game chat
    }
}