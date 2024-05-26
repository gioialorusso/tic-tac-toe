<?php

namespace App\Service;

use App\DTO\MoveRequest;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GameService implements GameServiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function makeMove(MoveRequest $moveRequest): Game
    {
        $game = $this->entityManager->getRepository(Game::class)->find($moveRequest->getGameId());
        if ($game === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, Game::GAME_NOT_FOUND);
        }
        $game->makeMove($moveRequest->getPlayer(), $moveRequest->getPosition());
        $this->entityManager->flush();
        return $game;
    }
}