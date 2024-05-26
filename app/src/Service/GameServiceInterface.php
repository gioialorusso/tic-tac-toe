<?php

namespace App\Service;

use App\DTO\MoveRequest;
use App\Entity\Game;

interface GameServiceInterface
{
    public function makeMove(MoveRequest $moveRequest): Game;
}