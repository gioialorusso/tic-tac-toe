<?php

namespace App\ApiResponse;

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
}