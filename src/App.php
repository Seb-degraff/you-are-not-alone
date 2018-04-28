<?php

namespace App;

use Longman\TelegramBot\Commands\UserCommands\JoinGameCommand;
use Longman\TelegramBot\Commands\UserCommands\StartGameCommand;
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

    public $outputInterface;

    public function __construct($outputInterface)
    {
        $this->storyContent = include (__DIR__ . "/../resources/story_content2.php");
        $this->outputInterface = $outputInterface;

        $this->fetcher = new Fetcher(Kernel::$instance->pdo);

//        $this->chat = $message->getChat();
//        $this->sender = $message->getFrom();
//        $this->player = $this->fetcher->getPlayerByTelegramId($this->sender->getId());
//
//        if ($this->player != null)
//            print ("I'm talking with " . $this->player->getDisplayName() . PHP_EOL);
//        else
//            print ("no current player" . PHP_EOL);
//
//
//        // NOTE: Not sure about the logic below, we're mixing chat game and player game which has the potential to be confusing
//        if ($this->chat->getId() < 0) {
//            // group chat -> find an existing game in this group or create one
//            $game = $this->fetcher->findGameByGroupChatId($this->chat->getId());
//            if ($game == null) {
//                $game = $this->fetcher->createGame($this->chat->getId());
//            }
//            $this->game = $game;
//        } else {
//            // personal conversation -> take the game the player is in
//            if ($this->player != null) {
//                $this->game = $this->fetcher->getCurrentGameForPlayer($this->player);
//            }
//        }
//
//        if ($this->game != null)
//            print ("the current game has the id " . $this->game->id . " and is linked to the channel \"{$this->game->chat_title}\", id {$this->game->chat_id}" .  PHP_EOL);
//        else
//            print ("no current game" . PHP_EOL);
    }

    public function checkIsGroupChat(int $chatId)
    {
        if ($chatId > 0) {
            $this->printChat($chatId, "Vous ne pouvez pas executer cette commande dans un message privé");
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
        return $this->fetcher->findAllPlayersInGame($this->game);
    }

    public function nextTurn(Game $game)
    {
        $players = $this->fetcher->findAllPlayersInGame($game);
        $notDeadPlayers = $this->getNotDeadPlayers($players);

        if (count($notDeadPlayers) == 0) {
            $this->printGameChat($game,"Dommage, personne n’a réussi à survivre à cette épreuve ! Fin de la partie.");
            $this->endGame($game);
            return;
        }

        if (count($notDeadPlayers) == 1) {
//            $this->sendImage($this->game->chat_id, 'http://gold.arrache.ch/public/images/butin.png');
            $this->printGameChat($game,'*' . $notDeadPlayers[0]->getDisplayName() . ":* Bien joué ! Vous êtes le dernier survivant ! prenez le butin, et faites vous plaisir !", true);
            $this->endGame($game);
            return;
        }

        $currentTurn = $game->current_turn;
        $this->fetcher->setGameCurrentTurn($game, ++$currentTurn);

        if ($currentTurn >= 5) {
            $this->sendImage($this->game->chat_id, 'http://gold.arrache.ch/public/images/butin.png');
            $this->printGameChat($game, "Bravo ! vous êtes venus à bout des épreuves ensemble ! vous pouvez vous partager le butin !");
            $this->endGame($game);
            return;
        }

        $this->wait(2);

        $currentStory = $this->getCurrentScenario($game);

        $this->printGameChat($game, strtoupper('*' . $currentStory['title'] . '*'), true);

        $this->sendImage($game->chat_id, $currentStory['image']);

        $this->printGameChat($game, $currentStory['story']);

        $this->wait(2);

        $this->printGameChat($game, 'Deux choix se présentent à vous:');
//        $this->wait(1);
        $this->printGameChat($game, "*Premier choix:* " . $currentStory['choice1'], true);
//        $this->wait(1);
        $this->printGameChat($game, "*Deuxième choix:* " . $currentStory['choice0'], true);

//        $this->wait(1);

        // reset player state
        foreach ($players as $player) {
            echo "resetting player " . $player->getDisplayName() . "!";
            $this->fetcher->playerSetActionChosen($player, null);
            $this->fetcher->playerSetHasDoneVision($player, false);
        }

        // choose damned one
        $damnedOneParticipantId = $notDeadPlayers[rand(0, count($notDeadPlayers) - 1)]->participant_id;
        $this->fetcher->game_SetDamnedOne($game, $damnedOneParticipantId);

        if (count($notDeadPlayers) == 2) {
            $this->printGameChat($game, "Vous n'êtes plus que deux joueurs, vous ne pouvez plus faire de voyance. Seuls les fantômes ou la chance pourront vous venir en aide.");
            foreach ($notDeadPlayers as $player) {
                $this->proposeActions($game, $player);
            }
        } else {
            $this->printGameChat($game, "Mais avant que vous preniez votre décision, je peux effectuer pour vous une vision. Parlez-moi en privé (/vision joueur)");

            foreach ($players as $player)
            {
                $visionOptions = [];

                foreach ($notDeadPlayers as $notDeadPlayer) {
                    if ($notDeadPlayer->user_id != $player->user_id) {
                        $visionOptions[] = $notDeadPlayer->getDisplayName();
                    }
                }

                $this->printPlayerChat($player, 'Choisissez une personne dont vous voulez voir le futur (/vision + nom). ' . join($visionOptions, ', '));
            }
        }
    }

    public function endGame(Game $game)
    {
        if ($game->isStarted()) {
            $this->fetcher->setGameCurrentTurn($game, -1);
            $this->printGameChat($game, "Voulez-vous recommencer ? /startGame");
        } else {
            $this->printGameChat($game, "Le jeu est déjà arrêté");
        }
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

    public function printGameChat(Game $game, $text = null, $markdown = false)
    {
        $this->outputInterface->printGameChat($game, $text, $markdown);
//        if ($this->game) {
//            return $this->printChat($this->game->chat_id, $text, $markdown);
//        } else {
//            print "No game channel to print \"$text\"";
//        }
    }

    public function printPlayerChat(Player $player, $text, $markdown = false)
    {
        $this->outputInterface->printPlayerChat($player, $text, $markdown);
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
    public function checkGameIsStarted(Game $game, $chat_id = null)
    {
        if ($chat_id == null) {
            $chat_id = $game->chat_id;
        }

        if (!$game->isStarted()) {
            $this->printChat($chat_id, "Le jeu n'a pas encore commencé. (" . StartGameCommand::NAME . ")");
            return false;
        }
        return true;
    }

    public function getCurrentScenario(Game $game)
    {
        return $this->storyContent['scenarios'][$game->current_turn];
    }

    public function joinGame (Game $game, Player $player)
    {
        if ($player->game_id !== null) {
            if ($player->game_id == $game->id) {
                $this->printGameChat($game,"Fuck you {$player->getDisplayName()}, you're already in the game 🖕");
                return false;
            } else {
                // if we are already participating in another game, leave it
                $previousGame = $this->fetcher->findGameById($player->game_id);
                if ($previousGame) {
                    $this->leaveGame($player, $previousGame);
                }
            }
        }

        $this->fetcher->player_SetGame($player, $game);
        $this->printGameChat($game,"welcome {$player->getDisplayName()}");

        return true;
    }

    public function leaveGame (Player $player, Game $game)
    {
        $this->fetcher->player_SetGame($player, null);

        $this->printGameChat($game, $player->getDisplayName() . " a quitté la partie.  Adieu 👋");
    }

    public function wait($seconds)
    {
//        if (Kernel::$instance->config["enable_delays"]) {
//            usleep($seconds * 1000 * 1000);
//        }
    }

    public function choose(Player $player, $actionChoice)
    {
        $game = $this->fetcher->findGameById($player->game_id);

        if (!$game) {
            $this->printPlayerChat($player, "Vous n'êtes pas actuellement dans un jeu 🤔");
            return;
        }

        if (!$game->isStarted()) {
            $this->printPlayerChat($player, "Le jeu n'a pas encore commencé. (/startGame)");
        }

        $this->fetcher->playerSetActionChosen($player, $actionChoice);

        $players = $this->fetcher->findAllPlayersInGame($game);

        $everybodyChoosed = true;
        foreach ($players as $player2)
        {
            if ($player2->action_chosen === null && !$player2->is_dead) {
                $everybodyChoosed = false;
            }
        }


        if ($everybodyChoosed) {
            $this->printGameChat($game, 'Tout le monde à choisi!');

            $somebodyIsDead = false;

            $currentScenario = $this->getCurrentScenario($game);

            foreach ($players as $player) {
                if ($player->is_dead)
                    continue;

                $action = (int) $player->action_chosen;
                $damned = $player->participant_id == $game->damned_one_participant_id;

                if ($action == $damned) {
                    // meurs
                    $somebodyIsDead = true;
                    $this->fetcher->player_SetIsDead($player, 1);
                    $this->printGameChat($game,"*" . $player->getDisplayName() . '*: ' . $currentScenario['looseChoice' . $action], true);
                } else {
                    $this->printGameChat($game, '*' . $player->getDisplayName() . '*: ' . $currentScenario['winChoice' . $action], true);
                }
            }

            if (!$somebodyIsDead) {
                $this->printGameChat($game, "Personne n'est mort! Bande de veinards");
            }

            $this->nextTurn($game);
        }
    }

    public function proposeActions(Game $game, Player $toPlayer)
    {
        $currentStory = $this->getCurrentScenario($game);
        $this->printPlayerChat($toPlayer, "Quelle action choisissez vous ?");
        $this->printPlayerChat($toPlayer, "/premierChoix: " . $currentStory['choice1']);
        $this->printPlayerChat($toPlayer, "/deuxiemeChoix: " . $currentStory['choice0']);
    }

    // NEW BELOW:
    public function newPlayer($name = "", $telegramId = null)
    {
        return $this->fetcher->newPlayer($name, $telegramId);
    }

    public function getOrCreatePlayer(int $telegramId)
    {
        $player = $this->fetcher->findPlayerByTelegramId($telegramId);
        if (!$player) {
            $player = $this->newPlayer("", $telegramId);
        }
        return $player;
    }

    public function getOrCreateGame(int $chatId)
    {
        $game = $this->fetcher->findGameByGroupChatId($chatId);
        if (!$game) {
            $game = $this->fetcher->createGame($chatId);
        }

        return $game;
    }

    public function startGame(Game $game)
    {
        $allPlayers = $this->fetcher->findAllPlayersInGame($game);

        if ($game->isStarted()) {
            $this->printGameChat($game, "Le jeu a déjà commencé. ( /endGame )");
            return;
        }

        if (count($allPlayers) < 2) {
            $this->printGameChat($game, "Nous sommes désolés, mais vous n'avez pas assez d'ami :(");
            return;
        }

        $text = "« Bienvenue dans “Donjons et Jargons“ Aventuriers ! Vous êtes ici pour trouver gloire et fortune, n’est-ce pas ? Eh bien, sachez que ce donjon est rempli d’obstacles et de créatures atroces ! Un certain nombre d’épreuves se présenteront à vous. Survivez, et le butin est à vous !\nÀ chaque épreuve, vous aurez deux choix. Un choix logique, et un choix plus risqué. L’un de vous mourra si il fait le choix le plus logique, mais il ne le sait pas. À chaque épreuve, vous pouvez me demander une vision du futur sur l’un des autres joueurs. À vous d’influencer les choix de vos compagnons, afin de les aider, ou au contraire, de les trahir ! Soyez vigilants ! Lorsque vous mourrez, vous ne serez plus que des fantômes, mais vous aurez accès à certaines informations, et vous pourrez les communiquer aux autres joueurs si vous le désirez. Bonne chance ! Mouahahahaha ! »";
        $this->printGameChat($game, $text, true);

        foreach ($allPlayers as $player) {
            $this->fetcher->player_SetIsDead($player, 0);
        }

        $this->nextTurn($game);
    }

    public function checkPlayerIsInGame(Player $player, Game $game)
    {
        if ($player->game_id != $game->id) {
            $this->printGameChat($game, "Vous ne faites pas partie du jeu pour le moment.");
            return false;
        }
        return true;
    }
}