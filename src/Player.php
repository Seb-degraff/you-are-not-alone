<?php

namespace App;

class Player
{
    public $participant_id;
    public $player_name;
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
        if ($this->player_name) {
            return $this->player_name;
        }

        if ($this->username) {
            return $this->username;
        }

        return $this->first_name;
    }
}