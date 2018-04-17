<?php

namespace App;

class Fetcher
{
    public $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

//    /**
//     * @return Game|null
//     */
//    public function getCurrentGame()
//    {
//        $statement = $this->pdo->query("SELECT * FROM games LIMIT 1");
//        $game = $statement->fetchObject(Game::class);
//
//        return $game;
//    }

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
        $statement = $this->pdo->query("SELECT game_participants.id as participant_id, user_id, first_name, last_name, username, action_chosen, has_done_vision, is_dead FROM game_participants JOIN user ON game_participants.user_id = user.id");
        $statement->setFetchMode(\PDO::FETCH_CLASS, Player::class);
        $players = $statement->fetchAll();

        return $players;
    }

    /**
     * @return Player[]
     */
    public function getAllPlayersFromGame(Game $game)
    {
        $sql = "SELECT game_participants.id as participant_id, user_id, first_name, last_name, user.username, action_chosen, has_done_vision, is_dead FROM games JOIN chat ON games.chat_id = chat.id JOIN game_participants ON game_participants.game_id = games.id JOIN user ON game_participants.user_id = user.id WHERE game_id = :game_id";
        $statement = $this->pdo->prepare($sql);
        $statement->setFetchMode(\PDO::FETCH_CLASS, Player::class);
        $statement->execute([':game_id' => $game->id]);
        $players = $statement->fetchAll();

        return $players;
    }


    public function getAllPlayersData()
    {
        $statement = $this->pdo->query("SELECT * FROM game_participants JOIN user ON game_participants.user_id = user.id");
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
        if ($action === null) {
            $statement = $this->pdo->prepare("UPDATE game_participants SET action_chosen = NULL WHERE game_participants.id = {$player->participant_id}");
        } else {
            $statement = $this->pdo->prepare("UPDATE game_participants SET action_chosen = :action WHERE game_participants.id = {$player->participant_id}");
        }
        $statement->execute([':action' => $action]);
    }

    public function playerSetIsDead(Player $player, $isDead)
    {
        $player->is_dead = $isDead;
        $statement = $this->pdo->query("UPDATE game_participants SET is_dead = $isDead WHERE game_participants.id = {$player->participant_id}");
        $statement->execute();
    }

    /**
     * @param Game $game
     * @param $user_id
     * @return Player
     */
    public function addGameParticipant(Game $game, $user_id)
    {
        $player = $this->getPlayerByTelegramId($user_id);
        if ($player != null)
            $this->removeGameParticipant($player);

        $statement = $this->pdo->prepare("INSERT INTO game_participants (game_id, user_id) VALUES (:game_id, :user_id)");
        $statement->execute([':game_id' => $game->id, 'user_id' => $user_id]);

        return $this->getPlayerByTelegramId($user_id);
    }

    public function removeGameParticipant(Player $player)
    {
        $statement = $this->pdo->prepare("DELETE FROM game_participants WHERE game_participants.id = {$player->participant_id}");
        $statement->execute();
    }

    /**
     * @param Player $player
     * @param int|bool $hasDoneVision
     */
    public function playerSetHasDoneVision($player, $hasDoneVision)
    {
        $hasDoneVision = (int) $hasDoneVision;

        $statement = $this->pdo->prepare("UPDATE game_participants SET has_done_vision = :has_done_vision WHERE game_participants.id = {$player->participant_id}");
        $statement->execute([':has_done_vision' => $hasDoneVision]);
    }



    //
    // GAME
    //

    public function setGameCurrentTurn(Game $game, $currentTurn)
    {
        $game->current_turn = $currentTurn;
        $statement = $this->pdo->prepare("UPDATE games SET current_turn = :current_turn WHERE games.id = {$game->id}");
        $statement->execute([':current_turn' => $currentTurn]);
    }

    public function gameSetDamnedOne(Game $game, $damnedOneParticipantId)
    {
        $statement = $this->pdo->prepare("UPDATE games SET damned_one_participant_id = :damned_one_participant_id WHERE games.id = :game_id");
        $statement->execute([
            ':damned_one_participant_id' => $damnedOneParticipantId,
            ':game_id' => $game->id,
        ]);
    }

    /**
     * @param $player
     * @return Game
     */
    public function getCurrentGameForPlayer(Player $player)
    {
        $statement = $this->pdo->prepare("SELECT games.id, games.damned_one_participant_id, games.current_turn, games.chat_id, chat.title as chat_title FROM games JOIN chat ON games.chat_id = chat.id JOIN game_participants ON game_participants.game_id = games.id WHERE game_participants.id = :player_id");
        $statement->execute(['player_id' => $player->participant_id]);
        $game = $statement->fetchObject(Game::class);

        return $game;
    }

    /**
     * @param int group chat id
     * @return Game
     */
    public function getGameFromGroupChatId($chatId)
    {
        $statement = $this->pdo->prepare("SELECT games.id, games.damned_one_participant_id, games.current_turn, games.chat_id, chat.title as chat_title FROM games JOIN chat ON games.chat_id = chat.id WHERE games.chat_id = :chat_id");
        $statement->execute(['chat_id' => $chatId]);
        $game = $statement->fetchObject(Game::class);

        return $game;
    }

    public function createGame($chatId)
    {
        $statement = $this->pdo->prepare("INSERT INTO games (chat_id) VALUES (:chat_id)");
        $statement->execute([':chat_id' => $chatId]);

        return $this->getGameFromGroupChatId($chatId);
    }
}