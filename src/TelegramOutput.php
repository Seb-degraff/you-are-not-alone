<?php

namespace App;

use Longman\TelegramBot\Request;

class TelegramOutput
{
    public function printGameChat(Game $game, $text, $markdown = false)
    {
        $this->printChat($game->chat_id, $text, $markdown);
    }

    public function printPlayerChat(Player $player, $text, $markdown = false)
    {
        $this->printChat($player->user_id, $text, $markdown);
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
}