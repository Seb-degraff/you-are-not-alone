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
class VisionCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'choosePlayer';
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
        $user = $message->getFrom();

        $chat_id = $message->getChat()->getId();

        $input = trim($message->getText(true));

        $app = App::$instance;
        $players = $app->fetcher->getAllPlayers();

        $text = "je n'ai pas compris";

        $mustChooseAction = false;

        foreach ($players as $player)
        {
            if (strtolower($player->getDisplayName()) == strtolower($input)) {
                $currentGame = $app->fetcher->getCurrentGame();

                if ($player->user_id == $user->getId()) {
                    $text = "Si vous saviez votre propre avenir vous ne pourriez pas l'accomplir. Choisissez quelqu'un d'autre que vous";
                    break;
                }

                if ($currentGame->damned_one_participant_id == $player->participant_id) {
                    $text = "Malheureusement {$player->getDisplayName()} va mourrir si il avance!!";
                } else {
                    $text = "{$player->getDisplayName()} est en sécurité";
                }

                $mustChooseAction = true;
            }
        }

        if ($mustChooseAction) {
            $text .= "\n";
            $text .= "Que voulez vous faire? (/chooseAction)";
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}