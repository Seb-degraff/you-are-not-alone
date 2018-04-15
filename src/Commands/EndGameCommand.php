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
class EndGameCommand extends UserCommand
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

        $chat_id = $message->getChat()->getId();

        $app = App::$instance;

        $app->printChat($chat_id, "le jeu à été stoppé");
        $app->endGame();
    }
}