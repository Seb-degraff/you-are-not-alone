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
        $user = $message->getFrom();

        $chat_id = $message->getChat()->getId();


        $app = App::$instance;

        if (!$app->checkGameIsStarted()) {
            $app->printChat($chat_id, "Le jeu n'a pas encore commencé. (/startGame)");
            return;
        }

        $currentPlayer = $app->fetcher->getPlayerByTelegramId($user->getId());

        $input = trim($message->getText(true));


        $actionChoice = null;

        if ($input === "0") {
            $actionChoice = 0;
        }
        if ($input === "1") {
            $actionChoice = 1;
        }

        if ($actionChoice === null) {
            $text = "je n'ai pas compris";
        } else {
            $text = "ok, ça roule";
            $app->fetcher->playerSetActionChosen($currentPlayer, $actionChoice);
        }

        $players = $app->fetcher->getAllPlayers();

        $everybodyChoosed = true;
        foreach ($players as $player2)
        {
            if ($player2->action_chosen === null && !$player2->is_dead) {
                $everybodyChoosed = false;
            }
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        Request::sendMessage($data);

        if ($everybodyChoosed) {
            $game = $app->fetcher->getCurrentGame();
            $text = 'Tout le monde à choisi!';

            Request::sendMessage(['chat_id' => $game->chat_id, 'text' => $text]);

            $somebodyIsDead = false;

            foreach ($players as $player) {
                $action = (bool) $player->action_chosen;
                $damned = $player->participant_id == $game->damned_one_participant_id;

                print_r(['$player->participant_id'  => $player->participant_id, '$game->damned_one_participant_id' => $game->damned_one_participant_id,  'player' => $player->getDisplayName(), 'action' => $action, 'damned' => $damned]);

                if ($action == $damned) {
                    // meurs
                    $somebodyIsDead = true;
                    $app->fetcher->playerSetIsDead($player, 1);
                    Request::sendMessage(['chat_id' => $game->chat_id, 'text' => $player->getDisplayName() . " est mort!"]);
                }
            }

            if (!$somebodyIsDead) {
                Request::sendMessage(['chat_id' => $game->chat_id, 'text' => "Personne n'est mort! Bande de veinards"]);
            }

            $app->newTurn();
        }
    }
}