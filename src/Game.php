<?php

namespace App;

class Game
{
    public $id;
    public $damned_one_participant_id;
    public $current_turn;
    public $is_started;
    public $chat_id;
    public $chat_title;

    public function isStarted()
    {
        print PHP_EOL . 'current turn: ' . $this->current_turn . PHP_EOL;

        return $this->current_turn >= 0;
    }
}