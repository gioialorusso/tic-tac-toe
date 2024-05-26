<?php

namespace App\ApiResponse;

use App\Entity\Game;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse{

    const string OK = 'OK';
    const string KO = 'KO';

    const string RESULT_KEY = 'result';
    const string RETURN_KEY = 'return';
    const string ERROR_MSG_KEY = 'error_msg';

    const string GAME_ID_KEY = 'game_id';
    const string BOARD_KEY = 'board';
    const string NEXT_PLAYER_KEY = 'next_player';
    const string WINNER_KEY = 'winner';

    public static function createOKResponse(array $data = []){

        return new JsonResponse(self::getOKPayload($data));
    }

    public static function createKOResponse(int $status_code = Response::HTTP_BAD_REQUEST, array $data = [], string $error_msg = ''){
        return new JsonResponse(self::getKOPayload($data, $error_msg), $status_code);
    }


    public static function getOKPayload(array $data = []): array
    {
        return [
            self::RESULT_KEY => self::OK,
            self::RETURN_KEY => $data
        ];
    }

    public static function getKOPayload(array $data = [], string $error_msg = ''): array
    {
        return [
            self::RESULT_KEY => self::KO,
            self::RETURN_KEY => $data,
            self::ERROR_MSG_KEY => $error_msg
        ];
    }

    public static function createOKGameResponse(Game $game): JsonResponse
    {
        return self::createOKResponse(self::getReturnGamePayload($game));
    }

    private static function getReturnGamePayload(Game $game): array
    {
        return [
            self::GAME_ID_KEY => $game->getId(),
            self::BOARD_KEY => $game->getBoard(),
            self::NEXT_PLAYER_KEY => $game->getNextPlayer(),
            self::WINNER_KEY => $game->getWinner()
        ];
    }
}