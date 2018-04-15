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
class StartGameCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'startGame';
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

//        if ($text === '') {
//            $text = 'Command usage: ' . $this->getUsage();
//        }

        $app = App::$instance;

        if ($app->checkGameIsStarted()) {
            $app->printChat($chat_id, "Le jeu à déjà commencé. ( /endGame )");
            return;
        }

        $pdo = App::$instance->pdo;

        $statement = $pdo->query("SELECT * FROM game_participants");
        $participants = $statement->fetchAll();

        if (count($participants) < 2) {
            $app->printChat($chat_id, "nous sommes désolés, mais vous n'avez pas assez d'ami :(");
        } else {
            $app = App::$instance;

            $app->endGame();

            $sql = "INSERT INTO games (chat_id) VALUES ($chat_id)";
            $statement2 = $app->pdo->query($sql);
            $statement2->execute();

            $players = $app->fetcher->getAllPlayers();
            foreach ($players as $player) {
                $app->fetcher->playerSetIsDead($player, 0);
            }

            $text = "« Bienvenue dans “Donjons et Jargons“ Aventuriers ! Vous êtes ici pour trouver gloire et fortune, n’est-ce pas ? Eh bien, sachez que ce donjon est rempli d’obstacles et de créatures atroces ! Un certain nombre d’épreuves se présenteront à vous. Survivez, et le butin est à vous !\nÀ chaque épreuve, vous aurez deux choix. Un choix logique, et un choix plus risqué. L’un de vous mourra si il fait le choix le plus logique, mais il ne le sait pas. À chaque épreuve, vous pouvez me demander une vision du futur sur l’un des autres joueurs. À vous d’influencer les choix de vos compagnons, afin de les aider, ou au contraire, de les trahir ! Soyez vigilants ! Lorsque vous mourrez, vous ne serez plus que des fantômes, mais vous aurez accès à certaines informations, et vous pourrez les communiquer aux autres joueurs si vous le désirez. Bonne chance ! Mouahahahaha ! »";

            $app->printChat($chat_id, $text);
            $app->nextTurn();
        }
    }
}