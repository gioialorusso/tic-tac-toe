<?php

namespace App\Controller;

use App\ApiResponse\ApiResponse;
use App\DTO\MoveRequest;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class GameController extends AbstractController
{

    //create constants for all error messages in functional validation
    const string GAME_NOT_FOUND = 'Game not found';
    const string NOT_PLAYERS_TURN = 'It is not player %s\'s turn.';
    const string BOARD_FULL = 'The board is full.';
    const string POSITION_OCCUPIED = 'This position is already occupied.';
    const string GAME_WON = 'The game is already won.';


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
            return ApiResponse::createKOResponse(Response::HTTP_NOT_FOUND, [], self::GAME_NOT_FOUND);
        }
        //wrong player's turn
        if($game->getNextPlayer() != $moveRequest->getPlayer()){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], sprintf(self::NOT_PLAYERS_TURN, $moveRequest->getPlayer()));
        }
        //move on full board
        if($game->isBoardFull()){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], self::BOARD_FULL);
        }
        //move on an already occupied position
        if($game->isPositionOccupied($moveRequest->getPosition())){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], self::POSITION_OCCUPIED);
        }
        //move on an already won game
        if(($game->isWon())){
            return ApiResponse::createKOResponse(Response::HTTP_BAD_REQUEST, [], self::GAME_WON);
        }

        return null;
    }

}
