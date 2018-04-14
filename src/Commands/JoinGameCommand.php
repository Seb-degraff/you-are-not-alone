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
class JoinGameCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'start_game';
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
        $pdo = $app->pdo;

        $userId = $user->getId();

        $statement = $pdo->query("SELECT * FROM game_participants WHERE user_id = $userId");

        $alreadyInDb = (bool) $statement->rowCount();

        if ($alreadyInDb) {
            $text = "Fuck you {$user->getFirstName()}, you're already in the game 🖕";
        } else {
            $pdo->exec("INSERT INTO game_participants (user_id, has_done_vision) VALUES ($userId, 0)");
            $text    = "welcome " . $user->getUsername();
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}