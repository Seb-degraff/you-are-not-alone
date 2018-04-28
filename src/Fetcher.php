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
    public function findPlayerByTelegramId($id)
    {
        $players = $this->findAllPlayers();

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
        $players = $this->findAllPlayers();

        foreach ($players as $player)
        {
            if ($player->getDisplayName() == $name)
            {
                return $player;
            }
        }

        return null;
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
            $statement->execute();
        } else {
            $statement = $this->pdo->prepare("UPDATE game_participants SET action_chosen = :action WHERE game_participants.id = {$player->participant_id}");
            $statement->execute([':action' => $action]);
        }
    }

    public function player_SetIsDead(Player $player, $isDead)
    {
        $player->is_dead = (int) $isDead;
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
        $player = $this->findPlayerByTelegramId($user_id);
        if ($player != null)
            $this->removeGameParticipant($player);

        $statement = $this->pdo->prepare("INSERT INTO game_participants (game_id, user_id) VALUES (:game_id, :user_id)");
        $statement->execute([':game_id' => $game->id, 'user_id' => $user_id]);

        return $this->findPlayerByTelegramId($user_id);
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

    public function game_SetDamnedOne(Game $game, $damnedOneParticipantId)
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
    public function findGameByGroupChatId($chatId)
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

        return $this->findGameByGroupChatId($chatId);
    }

    /**
     * @param string $playerName    The name after which the game knows this player
     * @param int|null $telegramId  Can be null for debug users
     * @return mixed
     */
    public function newPlayer(string $playerName, $telegramId = null)
    {
        $statement = $this->pdo->prepare("INSERT INTO game_participants (player_name, user_id) VALUES (:player_name, :user_id)");
        $statement->execute([':player_name' => $playerName, 'user_id' => $telegramId]);

        $playerId = $this->pdo->lastInsertId();
        echo $playerId;

        return $this->findPlayerById($playerId);
    }

    public function player_SetGame(Player $player, Game $game = null)
    {
        $player->game_id = $game != null ? $game->id : null;

        $statement = $this->pdo->prepare("UPDATE game_participants SET game_id = :game_id WHERE game_participants.id = :player_id");
        $statement->execute([':player_id' => $player->participant_id, ':game_id' => $game === null ? null : $game->id]);
    }

    /**
     * @return Player|null
     */
    public function findPlayerById($playerId)
    {
        $statement = $this->pdo->prepare("SELECT game_participants.id as participant_id, player_name, user_id, first_name, last_name, username, action_chosen, has_done_vision, is_dead, game_id FROM game_participants LEFT JOIN user ON game_participants.user_id = user.id WHERE game_participants.id = :player_id");
        $statement->execute([':player_id' => $playerId]);
        $player = $statement->fetchObject(Player::class);

        return $player;

    }

    /**
     * @return Player[]
     */
    public function findAllPlayers()
    {
        $statement = $this->pdo->query("SELECT game_participants.id as participant_id, player_name, user_id, first_name, last_name, username, action_chosen, has_done_vision, is_dead, game_id FROM game_participants LEFT JOIN user ON game_participants.user_id = user.id");
        $statement->setFetchMode(\PDO::FETCH_CLASS, Player::class);
        $players = $statement->fetchAll();

        return $players;
    }

    /**
     * @return Player[]
     */
    public function findAllPlayersInGame(Game $game)
    {
        $statement = $this->pdo->prepare("SELECT game_participants.id as participant_id, player_name, user_id, first_name, last_name, username, action_chosen, has_done_vision, is_dead, game_id FROM game_participants LEFT JOIN user ON game_participants.user_id = user.id WHERE game_id = :game_id");
        $statement->setFetchMode(\PDO::FETCH_CLASS, Player::class);
        $statement->execute(['game_id' => $game->id]);
        $players = $statement->fetchAll();

        return $players;
    }

    /**
     * @param int group chat id
     * @return Game|null
     */
    public function findGameById($gameId)
    {
        $statement = $this->pdo->prepare("SELECT games.id, games.damned_one_participant_id, games.current_turn, games.chat_id, chat.title as chat_title FROM games JOIN chat ON games.chat_id = chat.id WHERE games.id = :game_id");
        $statement->execute(['game_id' => $gameId]);
        $game = $statement->fetchObject(Game::class);

        return $game;
    }

    /**
     * @return Game[]
     */
    public function findAllGames()
    {
        $statement = $this->pdo->prepare("SELECT games.id, games.damned_one_participant_id, games.current_turn, games.chat_id, chat.title as chat_title FROM games JOIN chat ON games.chat_id = chat.id");
        $statement->setFetchMode(\PDO::FETCH_CLASS, Game::class);
        $statement->execute();
        $games = $statement->fetchAll();

        return $games;
    }

//    const PLAYER_FIELDS = "game_participants.id as participant_id, player_name, user_id, first_name, last_name, username, action_chosen, has_done_vision, is_dead";

//    public function getPlayerFields(): string
//    {
//        //return $this->getClassFields(Player::class);
//    }
//
//    public function getGameFields(): string
//    {
//        return $this->getClassFields(Game::class);
//    }
//
//    private function getClassFields($className): string
//    {
//        return join(',', array_keys(get_class_vars($className)));
//    }
}