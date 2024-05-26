<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class MoveRequest
{

    #[Assert\NotBlank(message: 'move.gameId.not_blank')]
    #[Assert\Type("string", message: 'move.gameId.type')]
    private $gameId;

    #[Assert\NotBlank(message: 'move.player.not_blank')]
    #[Assert\Type("integer", message: 'move.player.type')]
    #[Assert\Choice([1, 2], message: 'move.player.choice')]
    private $player;

    #[Assert\NotBlank(message: 'move.position.not_blank')]
    #[Assert\Type("integer", message: 'move.position.type')]
    #[Assert\Range(notInRangeMessage: 'move.position.range', min: 0, max: 8)]
    private $position;

    public function __construct($gameId, $player, $position)
    {
        $this->gameId = $gameId;
        $this->player = $player;
        $this->position = $position;
    }

    public function getGameId()
    {
        return $this->gameId;
    }

    public function setGameId($gameId)
    {
        $this->gameId = $gameId;
    }

    public function getPlayer()
    {
        return $this->player;
    }

    public function setPlayer($player)
    {
        $this->player = $player;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }
}
