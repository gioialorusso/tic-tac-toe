<?php

namespace App\Tests\ApiResponse;

use App\ApiResponse\ApiResponse;
use App\Entity\Game;
use PHPUnit\Framework\TestCase;

class ApiResponseTest extends TestCase
{
    public function testCreateOKResponse(): void
    {
        $data = ['key' => 'value'];
        $response = ApiResponse::createOKResponse($data);
        $this->assertEquals(200, $response->getStatusCode());
        $expected = [
            ApiResponse::RESULT_KEY => ApiResponse::OK,
            ApiResponse::RETURN_KEY => ['key' => 'value']
        ];

        $this->assertEquals(json_encode($expected), $response->getContent());
    }

    public function testCreateKOResponse(): void
    {
        $data = ['key' => 'value'];
        $error_msg = 'error message';
        $response = ApiResponse::createKOResponse(400, $data, $error_msg);
        $this->assertEquals(400, $response->getStatusCode());
        $expected = [
            ApiResponse::RESULT_KEY => ApiResponse::KO,
            ApiResponse::RETURN_KEY => ['key' => 'value'],
            ApiResponse::ERROR_MSG_KEY => 'error message'
        ];
        $this->assertEquals(json_encode($expected), $response->getContent());
    }

    public function testGetOKPayload(): void
    {
        $data = ['key' => 'value'];
        $payload = ApiResponse::getOKPayload($data);
        $expected = [
            ApiResponse::RESULT_KEY => ApiResponse::OK,
            ApiResponse::RETURN_KEY => ['key' => 'value']
        ];
        $this->assertEquals($expected, $payload);
    }

    public function testGetKOPayload(): void
    {
        $data = ['key' => 'value'];
        $error_msg = 'error message';
        $payload = ApiResponse::getKOPayload($data, $error_msg);
        $expected = [
            ApiResponse::RESULT_KEY => ApiResponse::KO,
            ApiResponse::RETURN_KEY => ['key' => 'value'],
            ApiResponse::ERROR_MSG_KEY => 'error message'
        ];
        $this->assertEquals($expected, $payload);
    }

    public function testCreateOKGameResponse(): void
    {
        $game = new Game();

        $game_id = uniqid('ttt');
        $board = Game::EMPTY_BOARD;

        $game->setId($game_id);
        $game->setBoard($board);
        $game->setNextPlayer(Game::PLAYER_1);
        $game->setWinner(Game::EMPTY_MARK);

        $response = ApiResponse::createOKGameResponse($game);

        $this->assertEquals(200, $response->getStatusCode());

        $expected = [
            ApiResponse::RESULT_KEY => ApiResponse::OK,
            ApiResponse::RETURN_KEY => [
                ApiResponse::GAME_ID_KEY => $game_id,
                ApiResponse::BOARD_KEY => $board,
                ApiResponse::NEXT_PLAYER_KEY => Game::PLAYER_1,
                ApiResponse::WINNER_KEY => Game::EMPTY_MARK
            ]
        ];

        $this->assertEquals(json_encode($expected), $response->getContent());
    }


}