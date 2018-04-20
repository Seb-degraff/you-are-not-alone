<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;


class VisionCommand extends UserCommand
{
    public function execute()
    {
        $message = $this->getMessage();
        $app = new App($message);

        $chat_id = $message->getChat()->getId();

        $input = trim($message->getText(true));

        if (!$app->checkGameIsStarted()) {
            $app->printChat($chat_id, "Le jeu n'a pas encore commencé. (/startGame)");
            return;
        }

        $players = $app->fetcher->getAllPlayers();

        $notDeadPlayers = $app->getNotDeadPlayers($players);

        if (count($notDeadPlayers) == 2 && !$app->player->is_dead) {
            Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Souvenez-vous, vous ne pouvez plus faire de vision. Bonne chance']);
            return;
        }

        if ($app->player->has_done_vision == 1) {
            Request::sendMessage(['chat_id' => $chat_id, 'text' => 'Vous avez déjà assez vu le futur pour ce tour.']);
            return;
        }

        $mustChooseAction = false;

        foreach ($players as $player)
        {
            if (strtolower($player->getDisplayName()) == strtolower($input)) {
                $currentGame = $app->game;

                if ($player->user_id == $app->player->user_id) {
                    $app->printChat($app->player->user_id, "Ce serait bien trop facile si vous saviez votre propre avenir. Pensez un peu aux autres et choisissez quelqu'un d'autre que vous"); // TODO changer la phrase
                    break;
                }

                $currentScenario = $app->getCurrentScenario();

                if ($currentGame->damned_one_participant_id == $player->participant_id) {
                    $app->printChat($app->player->user_id,"{$player->getDisplayName()} va mourrir " . $currentScenario['visionChoice1']);
                } else {
                    $app->printChat($app->player->user_id,"Il ne va rien arriver à {$player->getDisplayName()} " . $currentScenario['visionChoice1']);
                }

                $app->fetcher->playerSetHasDoneVision($app->player, true);
                $mustChooseAction = true;
            }
        }

        if ($mustChooseAction) {
            $app->wait(1);
            $app->proposeActions();
        } else {
            $app->printChat($app->player->user_id, "Je n'ai pas compris");
        }
    }
}