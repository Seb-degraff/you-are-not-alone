<?php

namespace App;

use Longman\TelegramBot\Commands\UserCommands\JoinGameCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

class App
{
//    /**
//     * @var App
//     */
//    public static $instance;
    public $storyContent;
    public $fetcher;

    public $chat;
    public $sender;
    public $player;
    public $game;

    public function __construct(Message $message)
    {
        $this->storyContent = include (__DIR__ . "/../resources/story_content2.php");

        $this->fetcher = new Fetcher(Kernel::$instance->pdo);

        $this->chat = $message->getChat();
        $this->sender = $message->getFrom();
        $this->player = $this->fetcher->getPlayerByTelegramId($this->sender->getId());

        if ($this->player != null)
            print ("I'm talking with " . $this->player->getDisplayName() . PHP_EOL);
        else
            print ("no current player" . PHP_EOL);


        if ($this->chat->getId() < 0) {
            // group chat -> find an existing game in this group or create one
            $game = $this->fetcher->getGameFromGroupChatId($this->chat->getId());
            if ($game == null) {
                $game = $this->fetcher->createGame($this->chat->getId());
            }
            $this->game = $game;
        } else {
            // personal conversation -> take the game the player is in
            if ($this->player != null) {
                $this->game = $this->fetcher->getCurrentGameForPlayer($this->player);
            }
        }


        if ($this->game != null)
            print ("the current game has the id " . $this->game->id . " and is linked to the channel \"{$this->game->chat_title}\", id {$this->game->chat_id}" .  PHP_EOL);
        else
            print ("no current game" . PHP_EOL);
    }

    public function checkIsInGroupChat()
    {
        print $this->chat->getId();
        if ($this->chat->getId() > 0) {
            $this->printChat($this->chat->id, "Vous ne pouvez pas executer cette commande dans un message privé");
            return false;
        } else {
            return true;
        }
    }

    public function checkHasPlayer()
    {
        if (!$this->player) {
            $this->printChat($this->chat->getId(), "Vous n'êtes pas encore dans un jeu pour le moment. Utilisez la commande " . JoinGameCommand::NAME . " (debug: no player in db)");
            return false;
        } else {
            return true;
        }
    }

    public function checkHasGame()
    {
        if (!$this->player) {
            $this->printChat($this->chat->getId(), "Vous n'êtes pas encore dans un jeu pour le moment. Utilisez la commande " . JoinGameCommand::NAME . " (debug: no game for this user)");
            return false;
        } else {
            return true;
        }
    }

    /**
     * @return Player[]
     */
    public function getAllPlayersInCurrentGame()
    {
        return $this->fetcher->getAllPlayersFromGame($this->game);
    }

    public function nextTurn()
    {
        $players = $this->fetcher->getAllPlayers();

        $notDeadPlayers = $this->getNotDeadPlayers($players);

        if (count($notDeadPlayers) == 0) {
            $this->printGameChat("Dommage, personne n’a réussi à survivre à cette épreuve ! Fin de la partie.");
            $this->endGame();
            return;
        }

        if (count($notDeadPlayers) == 1) {
            $this->sendImage($this->game->chat_id, 'http://gold.arrache.ch/public/images/butin.png');
//            $this->printGameChat($notDeadPlayers[0]->getDisplayName() . " gagne la partie et le trésor, félicitation!!!");
            $this->printGameChat('*' . $notDeadPlayers[0]->getDisplayName() . ":* Bien joué ! Vous êtes le dernier survivant ! prenez le butin, et faites vous plaisir !");
            $this->endGame();
            return;
        }

        $currentTurn = $this->game->current_turn;
        $this->fetcher->setGameCurrentTurn($this->game, ++$currentTurn);

        if ($currentTurn >= 5) {
            $this->sendImage($this->game->chat_id, 'http://gold.arrache.ch/public/images/butin.png');
            $this->printGameChat("Bravo ! vous êtes venus à bout des épreuves ensemble ! vous pouvez vous partager le butin !");
            $this->endGame();
            return;
        }

        $this->wait(2);

        $currentStory = $this->getCurrentScenario();

        $this->printGameChat(strtoupper('*' . $currentStory['title'] . '*'), true);

        $this->sendImage($this->game->chat_id, $currentStory['image']);

        $this->printGameChat($currentStory['story']);

        $this->wait(2);

        $this->printGameChat('Deux choix se présentent à vous:');
//        $this->wait(1);
        $this->printGameChat("*Premier choix:* " . $currentStory['choice1'], true);
//        $this->wait(1);
        $this->printGameChat("*Deuxième choix:* " . $currentStory['choice0'], true);

//        $this->wait(1);

        if (count($notDeadPlayers) == 2) {
            $this->printGameChat("Vous n'êtes plus que deux joueurs, vous ne pouvez plus faire de voyance. Seuls les fantômes ou la chance pourront vous venir en aide.");
            $this->proposeActions();
            return;
        } else {
            $this->printGameChat("Mais avant que vous preniez votre décision, je peux effectuer pour vous une vision. Parlez-moi en privé (/vision joueur)");
        }

        $this->wait(2);

        $damnedOneParticipantId = $notDeadPlayers[rand(0, count($notDeadPlayers) - 1)]->participant_id;

        $this->fetcher->gameSetDamnedOne($this->game, $damnedOneParticipantId);


        foreach ($players as $key => $player) {
            $userId = $player->user_id;

            // reset player state
            $this->fetcher->playerSetActionChosen($player, null);
            $this->fetcher->playerSetHasDoneVision($player, false);

            $visionOptions = [];

            foreach ($notDeadPlayers as $notDeadPlayer) {
                if ($notDeadPlayer->user_id != $player->user_id) {
                    $visionOptions[] = $notDeadPlayer->getDisplayName();
                }
            }

            $reponse = $this->printChat($userId, 'Choisissez une personne dont vous voulez voir le futur (/vision + nom). ' . join($visionOptions, ', '));

//                if ($key == $chosenOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le chosen one']);
//                } else if ($key == $damnedOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le damned one']);
//                }
        }
    }

    public function endGame()
    {
        if ($this->game->isStarted()) {
            $this->fetcher->setGameCurrentTurn($this->game, -1);
            $this->printGameChat("Voulez-vous recommencer ? /startGame");
        } else {
            $this->printGameChat("Le jeu est déjà arrêté");
        }
    }

    public function leaveGame (Player $player, Game $game)
    {
        $this->fetcher->removeGameParticipant($this->player);

        $this->printChat($game->chat_id, $player->getDisplayName() . " a quitté la partie.  Adieu 👋");
    }

    public function removePlayer(Player $player)
    {
        $this->fetcher->removeGameParticipant($player);
    }

    public function getNotDeadPlayers($players)
    {
        $notDeadPlayers = [];

        foreach ($players as $player) {
            if ($player->is_dead == 0) {
                $notDeadPlayers[] = $player;
            }
        }

        return $notDeadPlayers;
    }

    public function printGameChat($text = null, $markdown = false)
    {
        if ($this->game) {
            return $this->printChat($this->game->chat_id, $text, $markdown);
        } else {
            print "No game channel to print \"$text\"";
        }
    }

    public function printChat($chat_id, $text, $markdown = false)
    {
        $data['chat_id'] = $chat_id;
        $data['text'] = $text;
        if ($markdown)
            $data['parse_mode'] = 'Markdown';

        $response = Request::sendMessage($data);

        if (!$response->isOk()) {
            echo "Error when sending $text to $chat_id." . PHP_EOL;
            print_r($response);
        }

        return $response;
    }

    public function sendImage($chat_id, $imageUrl)
    {
        if (!Kernel::$instance->config['enable_images'])
            return;

        $data['chat_id'] = $chat_id;
        $data['photo'] = $imageUrl;

        $response = Request::sendPhoto($data);

        if (!$response->isOk()) {
            echo "Error when sending image $imageUrl to $chat_id." . PHP_EOL;
            print_r($response);
        }

        return $response;
    }

    /**
     * @return bool
     */
    public function checkGameIsStarted()
    {
        return $this->game->isStarted();
    }

    public function getCurrentScenario()
    {
        $game = $this->game;

        return $this->storyContent['scenarios'][$game->current_turn];
    }

    public function joinGame(Game $game, $userId)
    {

        if ($this->player) {
            if ($this->player->game_id == $game->id) {
                $this->printGameChat("Fuck you {$this->player->getDisplayName()}, you're already in the game 🖕");
                return false;
            } else {
                // leave current game if we have another one
                $previousGame = $this->fetcher->getCurrentGameForPlayer($this->player);
                if ($previousGame) {
                    $this->leaveGame($this->player, $previousGame);
                }
            }
        }

        $this->player = $this->fetcher->addGameParticipant($game, $userId);
        $this->printGameChat("welcome {$this->player->getDisplayName()}");

        return true;
    }

    public function wait($seconds)
    {
//        if (Kernel::$instance->config["enable_delays"]) {
//            usleep($seconds * 1000 * 1000);
//        }
    }

    public function choose($actionChoice)
    {
        $this->fetcher->playerSetActionChosen($this->player, $actionChoice);

        $players = $this->fetcher->getAllPlayers();

        $everybodyChoosed = true;
        foreach ($players as $player2)
        {
            if ($player2->action_chosen === null && !$player2->is_dead) {
                $everybodyChoosed = false;
            }
        }


        if ($everybodyChoosed) {
            $game = $this->game;

            $this->printChat($game->chat_id, 'Tout le monde à choisi!');

            $somebodyIsDead = false;

            $currentScenario = $this->getCurrentScenario();

            foreach ($players as $player) {
                if ($player->is_dead)
                    continue;

                $action = (int) $player->action_chosen;
                $damned = $player->participant_id == $game->damned_one_participant_id;

                if ($action == $damned) {
                    // meurs
                    $somebodyIsDead = true;
                    $this->fetcher->playerSetIsDead($player, 1);
                    $this->printGameChat("*" . $player->getDisplayName() . '*: ' . $currentScenario['looseChoice' . $action], true);
                } else {
                    $this->printGameChat('*' . $player->getDisplayName() . '*: ' . $currentScenario['winChoice' . $action], true);
                }
            }

            if (!$somebodyIsDead) {
                $this->printGameChat($game->chat_id, "Personne n'est mort! Bande de veinards");
            }

            $this->nextTurn();
        }
    }

    public function proposeActions()
    {
        $currentStory = $this->getCurrentScenario();
        $this->printChat($this->player->user_id, "Quelle action choisissez vous ?");
        $this->printChat($this->player->user_id, "/premierChoix: " . $currentStory['choice1']);
        $this->printChat($this->player->user_id, "/deuxiemeChoix: " . $currentStory['choice0']);
    }
}