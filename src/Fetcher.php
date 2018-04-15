<?php

namespace App;

class Fetcher
{

    /**
     * @return Game|null
     */
    public function getCurrentGame()
    {
        $statement = App::$instance->pdo->query("SELECT * FROM games LIMIT 1");
        $game = $statement->fetchObject(Game::class);

        return $game;
    }

    /**
     * @param int $id
     * @return Player|null
     */
    public function getPlayerByTelegramId($id)
    {
        $players = $this->getAllPlayers();

        foreach ($players as $player)
        {
            if ($player->user_id == $id)
            {
                return $player;
            }
        }

        return null;
    }

    /**
     * @param $name
     * @return Player|null
     */
    public function getPlayerByName($name)
    {
        $players = $this->getAllPlayers();

        foreach ($players as $player)
        {
            if ($player->getDisplayName() == $name)
            {
                return $player;
            }
        }

        return null;
    }

    /**
     * @return Player[]
     */
    public function getAllPlayers()
    {
        $statement = App::$instance->pdo->query("SELECT game_participants.id as participant_id, user_id, first_name, last_name, username, action_chosen, has_done_vision, is_dead FROM game_participants JOIN user ON game_participants.user_id = user.id");
        $statement->setFetchMode(\PDO::FETCH_CLASS, Player::class);
        $players = $statement->fetchAll();

        return $players;
    }


    public function getAllPlayersData()
    {
        $statement = App::$instance->pdo->query("SELECT * FROM game_participants JOIN user ON game_participants.user_id = user.id");
        $all = $statement->fetchAll();
        return $all;
    }

    public function getAllPlayerNames()
    {
        $players = $this->getAllPlayersData();

        $names = [];

        foreach ($players as $player) {
            $names[] = isset($player['username']) ? $player['username'] : $player['first_name'];
        }

        return $names;
    }

    /**
     * @param Player $player
     * @param int $action
     */
    public function playerSetActionChosen(Player $player, $action)
    {
        $player->action_chosen = $action;
        $statement = App::$instance->pdo->prepare("UPDATE game_participants SET action_chosen = :action WHERE game_participants.id = {$player->participant_id}");
        $statement->execute([':action' => $action]);
    }

    public function playerSetIsDead(Player $player, $isDead)
    {
        $player->is_dead = $isDead;
        $statement = App::$instance->pdo->query("UPDATE game_participants SET is_dead = $isDead WHERE game_participants.id = {$player->participant_id}");
        $statement->execute();
    }

    public function removeGameParticipant(Player $player)
    {
        $statement = App::$instance->pdo->prepare("DELETE FROM game_participants WHERE game_participants.id = {$player->participant_id}");
        $statement->execute();
    }

    /**
     * @param Player $player
     * @param int|bool $hasDoneVision
     */
    public function playerSetHasDoneVision($player, $hasDoneVision)
    {
        $hasDoneVision = (int) $hasDoneVision;

        $statement = App::$instance->pdo->prepare("UPDATE game_participants SET has_done_vision = :has_done_vision WHERE game_participants.id = {$player->participant_id}");
        $statement->execute([':has_done_vision' => $hasDoneVision]);
    }

    public function setGameCurrentTurn(Game $game, $currentTurn)
    {
        $game->current_turn = $currentTurn;
        $statement = App::$instance->pdo->prepare("UPDATE games SET current_turn = :current_turn WHERE games.id = {$game->id}");
        $statement->execute([':current_turn' => $currentTurn]);
    }
}