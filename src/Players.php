<?php

namespace App;

class Players
{
    public function getAllPlayers()
    {
        $statement = App::$instance->pdo->query("SELECT * FROM game_participants JOIN user ON game_participants.user_id = user.id");
        $all = $statement->fetchAll();
        return $all;
    }

    public function getAllPlayerNames()
    {
        $players = $this->getAllPlayers();

        $names = [];

        foreach ($players as $player) {
            $names[] = isset($player['username']) ? $player['username'] : $player['first_name'];
        }

        return $names;
    }
}