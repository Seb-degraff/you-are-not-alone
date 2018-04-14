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

        $pdo = $this->telegram->pdo;

        $userId = $user->getId();

        $statement = $pdo->query("SELECT * FROM game_participants");
        $participants = $statement->fetchAll();

        if (count($participants) < 2) {
            $text = "nous sommes désolés, mais vous n'avez pas assez d'ami :(";
        } else {
//            shuffle($participants);

//            $chosenOneIndex = 0;
//            $damnedOneIndex = 1;

            $playerNamesList = App::$instance->players->getAllPlayerNames();

//            print_r($playerNamesList);


            foreach ($participants as $key => $participant) {
                $userId = $participant['user_id'];

                $response = Request::sendMessage(['chat_id' => $userId, 'text' => 'Choisissez une personne dont vous voulez voir le futur. ' . join($playerNamesList, ', ') ] );

//                if ($key == $chosenOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le chosen one']);
//                } else if ($key == $damnedOneIndex) {
//                    Request::sendMessage(['chat_id' => $userId, 'text' => 'Tu es le damned one']);
//                }
            }


            $text = "Bienvenue Aventuriers ! Vous êtes ici pour trouver gloire et fortune, n’est-ce pas ? Eh bien, sachez que ce donjon est rempli d’obstacles et de créatures atroces ! Vous êtes des explorateurs aguerris, et vous n’aurez pas trop de difficulté à déjouer les nombreux pièges devant vous. Mais attention à ne pas être trop confiants ! À chaque épreuve, l’un de vous quatre a une chance de mourir, et il ne restera qu’un heureux explorateur à la fin de cette quête ! Mouahahahahaha !";
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}