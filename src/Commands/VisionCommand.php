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

        if (!$app->checkGameIsStarted()) {
            $app->printChat($chat_id, "Le jeu n'a pas encore commencé. (/startGame)");
            return;
        }

        $players = $app->fetcher->getAllPlayers();

        $notDeadPlayers = $app->getNotDeadPlayers($players);

        if (count($notDeadPlayers) == 2) {
            Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Souvenez-vous, vous ne pouvez plus faire de vision. Bonne chance']);
            return;
        }

        $currentPlayer = $app->fetcher->getPlayerByTelegramId($user->getId());

        if ($currentPlayer->has_done_vision == 1) {
            Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Vous avez déjà assez vu le futur pour ce tour.']);
            return;
        }

        $mustChooseAction = false;

        foreach ($players as $player)
        {
            if (strtolower($player->getDisplayName()) == strtolower($input)) {
                $currentGame = $app->fetcher->getCurrentGame();

                if ($player->user_id == $user->getId()) {
                    $app->printChat($currentPlayer->user_id, "Si vous saviez votre propre avenir vous ne pourriez pas l'accomplir. Choisissez quelqu'un d'autre que vous"); // TODO changer la phrase
                    break;
                }

                $currentScenario = $app->getCurrentScenario();

                if ($currentGame->damned_one_participant_id == $player->participant_id) {
                    $app->printChat($currentPlayer->user_id,"{$player->getDisplayName()} va mourrir " . $currentScenario['visionChoice1']);
                } else {
                    $app->printChat($currentPlayer->user_id,"Il ne va rien arriver à {$player->getDisplayName()} " . $currentScenario['visionChoice1']);
                }

                $app->fetcher->playerSetHasDoneVision($currentPlayer, true);
                $mustChooseAction = true;
            }
        }

        if ($mustChooseAction) {
            sleep(1);
            $app->printChat($currentPlayer->user_id, "Quelle action choisissez vous ? /action + 1 ou 2");
        } else {
            $app->printChat($currentPlayer->user_id, "Je n'ai pas compris");
        }
    }
}