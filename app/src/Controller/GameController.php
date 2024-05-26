<?php

namespace App\Controller;

use App\ApiResponse\ApiResponse;
use App\DTO\MoveRequest;
use App\Entity\Game;
use App\Service\GameService;
use App\Service\GameServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class GameController extends AbstractController
{

    #[Route('/api/game/start', name: 'start_game', methods: ['POST'])]
    public function start(EntityManagerInterface $entityManager): JsonResponse
    {
        $game = new Game();
        $entityManager->persist($game);
        $entityManager->flush();

        return ApiResponse::createOKGameResponse($game);

    }

    #[Route('/api/game/move', name: 'make_move', methods: ['POST'])]
    public function move(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator, GameServiceInterface $gameService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);


        $moveRequest = new MoveRequest(
            $data['game_id'] ?? null,
            $data['player'] ?? null,
            $data['position'] ?? null
        );

        // Formally validate the DTO
        $errorResponse = $this->validateRequest($validator, $moveRequest);
        if(!is_null($errorResponse)){
            return $errorResponse;
        }

        // Functional validation is done inside Game::makeMove
        try{
            $game = $gameService->makeMove($moveRequest);
            return ApiResponse::createOKGameResponse($game);
        }catch (HttpException $e){
            return ApiResponse::createKOResponse($e->getStatusCode(), [], $e->getMessage());
        }

    }

    private function validateRequest(ValidatorInterface $validator, MoveRequest $moveRequest): ?JsonResponse
    {
        $violations = $validator->validate($moveRequest);
        if (count($violations) > 0) {
            $error_msg = "";
            foreach ($violations as $violation) {
                $error_msg .= $violation->getMessage();
            }
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], $error_msg);
        }
        return null;

    }



}
