<?php

namespace App\Controller;

use App\ApiResponse\ApiResponse;
use App\DTO\MoveRequest;
use App\Entity\Game;
use App\Entity\User;
use App\Form\MoveRequestType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Valid;
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
    public function move(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
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

        //Functionally validate the DTO + $game
        $game = $entityManager->getRepository(Game::class)->find($moveRequest->getGameId());
        $errorResponse = $this->functionalValidateRequest($moveRequest, $game);
        if(!is_null($errorResponse)){
            return $errorResponse;
        }

        //Our move is valid! Let's proceed saving the move and checking the winner
        $game->makeMove($moveRequest->getPlayer(), $moveRequest->getPosition());
        $entityManager->flush();

        return ApiResponse::createOKGameResponse($game);
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

    private function functionalValidateRequest( MoveRequest $moveRequest, ?Game $game): ?JsonResponse
    {
        //game not found
        if(is_null($game)){
            return ApiResponse::createKOResponse(Response::HTTP_NOT_FOUND, [], 'Game not found');
        }
        //wrong player's turn
        if($game->getNextPlayer() != $moveRequest->getPlayer()){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], "It is not player {$moveRequest->getPlayer()}'s turn.");
        }
        //move on full board
        if($game->isBoardFull()){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], 'The board is full.');
        }
        //move on an already occupied position
        if($game->isPositionOccupied($moveRequest->getPosition())){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], 'This position is already occupied.');
        }
        //move on an already won game
        if(($game->isWon())){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], 'The game is already won.');
        }

        return null;
    }

}
