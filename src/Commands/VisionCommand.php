<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use App\App;
use App\TelegramOutput;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;


class VisionCommand extends UserCommand
{
    public function execute()
    {
        $out = new TelegramOutput();
        $app = new App($out);

        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();

        $input = trim($message->getText(true));

        $player = $app->getOrCreatePlayer($message->getFrom()->getId());

        $game = $app->fetcher->getCurrentGameForPlayer($player);

        if (!$app->checkGameIsStarted($game, $chat_id)) {
            return;
        }

        if (!$app->checkPlayerIsInGame($player, $game)) {
            return;
        }


        $players = $app->fetcher->findAllPlayers();
        $notDeadPlayers = $app->getNotDeadPlayers($players);

        if (count($notDeadPlayers) == 2 && !$player->is_dead) {
            $app->printChat($chat_id, 'Souvenez-vous, vous ne pouvez plus faire de vision. Bonne chance');
            return;
        }

        if ($player->has_done_vision == 1) {
            $app->printChat($chat_id, 'Vous avez déjà assez vu le futur pour ce tour.');
            return;
        }

        $selectedPlayer = null;

        foreach ($players as $otherPlayer)
        {
            if (strtolower($otherPlayer->getDisplayName()) == strtolower($input)) {
                $selectedPlayer = $otherPlayer;
            }
        }

        if (!$selectedPlayer) {
            $app->printChat($player->user_id, "Je n'ai pas compris");
            return;
        }

        if ($selectedPlayer->participant_id == $player->participant_id) {
            $app->printChat($player->user_id, "Ce serait bien trop facile si vous saviez votre propre avenir. Pensez un peu aux autres et choisissez quelqu'un d'autre que vous"); // TODO changer la phrase
            return;
        }

        $currentScenario = $app->getCurrentScenario($game);

        if ($game->damned_one_participant_id == $selectedPlayer->participant_id) {
            $app->printPlayerChat($player,"{$selectedPlayer->getDisplayName()} va mourrir " . $currentScenario['visionChoice1']);
        } else {
            $app->printPlayerChat($player,"Il ne va rien arriver à {$selectedPlayer->getDisplayName()} " . $currentScenario['visionChoice1']);
        }

        $app->fetcher->playerSetHasDoneVision($player, true);

        $app->wait(1);
        $app->proposeActions($game, $player);
    }
}