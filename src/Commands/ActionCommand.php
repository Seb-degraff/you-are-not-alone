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
class ActionCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'chooseAction';
    /**
     * @var string
     */
    protected $description = 'Start a game';
    /**
     * @var string
     */
    protected $usage = 'yes';
    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
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


        $actionChoice = null;

        if ($input === "1") {
            $actionChoice = 1;
        }
        if ($input === "2") {
            $actionChoice = 0;
        }

        if ($actionChoice === null) {
            $app->printChat($chat_id, "je n'ai pas compris");
            return;
        }

        $app->printChat($chat_id, "Très bien...");

        $app->choose($actionChoice);
    }
}