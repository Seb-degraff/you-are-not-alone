<?php

namespace App;

use Longman\TelegramBot\Request;

class BufferOutput
{
    public $messages = [];

    public function printGameChat(Game $game, $text, $markdown = false)
    {
        $this->messages[] = $this->formatConsoleOutput("Game #{$game->id} chat:", $text);
    }


    public function printPlayerChat(Player $player, $text, $markdown = false)
    {
        $this->messages[] = $this->formatConsoleOutput("{$player->getDisplayName()} chat:", $text);
    }

    private function formatConsoleOutput($head, $body)
    {
        $col1Width = 20;
        $col2Width = 100;
        return str_replace("\n", "\n" . str_repeat(' ', $col1Width), str_pad($head, $col1Width, " ") . wordwrap($body, $col2Width, "\n") . "\n");
    }
}