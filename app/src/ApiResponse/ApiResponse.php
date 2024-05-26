<?php

namespace App\ApiResponse;

use App\Entity\Game;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse{

    const OK = 'OK';
    const KO = 'KO';

    public static function createOKResponse(array $data = []){

        return new JsonResponse(self::getOKPayload($data));
    }

    public static function createKOResponse(int $status_code = Response::HTTP_BAD_REQUEST, array $data = [], string $error_msg = ''){
        return new JsonResponse(self::getKOPayload($data, $error_msg), $status_code);
    }


    public static function getOKPayload(array $data = []): array
    {
        return [
            'result' => self::OK,
            'return' => $data
        ];
    }

    public static function getKOPayload(array $data = [], string $error_msg = ''): array
    {
        return [
            'result' => self::KO,
            'return' => $data,
            'error_msg' => $error_msg
        ];
    }

    public static function createOKGameResponse(Game $game): JsonResponse
    {
        return self::createOKResponse(self::getReturnGamePayload($game));
    }

    private static function getReturnGamePayload(Game $game): array
    {
        return [
            'game_id' => $game->getId(),
            'board' => $game->getBoard(),
            'next_player' => $game->getNextPlayer(),
            'winner' => $game->getWinner()
        ];
    }
}