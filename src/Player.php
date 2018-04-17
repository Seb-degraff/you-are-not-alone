<?php

namespace App;

class Player
{
    public $participant_id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $username;
    public $action_chosen;
    public $has_done_vision;
    public $is_dead;
    public $game_id;

    public function getDisplayName()
    {
        if ($this->username)
        {
            return $this->username;// . ($this->is_dead ? ' ☠️' : '');
        }
        return $this->first_name;
    }
}