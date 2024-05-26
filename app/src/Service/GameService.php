<?php

namespace App\Service;

use App\DTO\MoveRequest;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Base;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class GameService extends BaseService implements GameServiceInterface
{

    public function createGame(): Game
    {
        $game = new Game();
        $this->getEntityManager()->persist($game);
        $this->getEntityManager()->flush();
        return $game;
    }

    public function makeMove(MoveRequest $moveRequest): Game
    {
        $game = $this->getEntityManager()->getRepository(Game::class)->find($moveRequest->getGameId());
        if ($game === null) {
            throw new HttpException(Response::HTTP_NOT_FOUND, Game::GAME_NOT_FOUND);
        }
        $game->makeMove($moveRequest->getPlayer(), $moveRequest->getPosition());
        $this->getEntityManager()->flush();
        return $game;
    }
}