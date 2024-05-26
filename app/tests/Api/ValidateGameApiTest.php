<?php

namespace App\Tests\Api;

use App\Entity\Game;
use App\Tests\Common\DatabaseFixture;
use Symfony\Component\HttpFoundation\Response;

class ValidateGameApiTest extends ApiTestCase
{
    public function testValidateMove(): void
    {
        $token = $this->getToken(['username' => 'admin', 'password' => 'password']);
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        //game_id missing
        $body_move = [
            'player' => 1,
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'game_id: ' . ApiTestCase::VALIDATION_MESSAGE_NOT_BLANK);

        //game_id wrong type
        $body_move = [
            'game_id' => 10,
            'player' => 1,
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'game_id: ' . ApiTestCase::VALIDATION_MESSAGE_TYPE_STRING);

        //player missing
        $body_move = [
            'game_id' => '1',
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'player: ' . ApiTestCase::VALIDATION_MESSAGE_NOT_BLANK);

        //player not in 1, 2
        $body_move = [
            'game_id' => '1',
            'player' => 3,
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'player: ' . ApiTestCase::VALIDATION_MESSAGE_TYPE_CHOICE);

        //player wrong type

        $body_move = [
            'game_id' => '1',
            'player' => '1',
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'player: ' . ApiTestCase::VALIDATION_MESSAGE_TYPE_INT . "player: " . ApiTestCase::VALIDATION_MESSAGE_TYPE_CHOICE);

        //position missing
        $body_move = [
            'game_id' => '1',
            'player' => 1
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'position: ' . ApiTestCase::VALIDATION_MESSAGE_NOT_BLANK);

        //position not in 0, 1, 2, 3, 4, 5, 6, 7, 8
        $body_move = [
            'game_id' => '1',
            'player' => 1,
            'position' => 9
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'position: ' . ApiTestCase::VALIDATION_MESSAGE_RANGE_0_8);

        $body_move = [
            'game_id' => '1',
            'player' => 1,
            'position' => -1
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'position: ' . ApiTestCase::VALIDATION_MESSAGE_RANGE_0_8);


        $body_move = [
            'game_id' => '1',
            'player' => 1,
            'position' => 100
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'position: ' . ApiTestCase::VALIDATION_MESSAGE_RANGE_0_8);


        //position wrong type
        $body_move = [
            'game_id' => '1',
            'player' => 1,
            'position' => '3'
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(400, 'position: ' . ApiTestCase::VALIDATION_MESSAGE_TYPE_INT);


        //everything formally correct, but unexistent game
        $body_move = [
            'game_id' => uniqid('ttt'),
            'player' => 1,
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(Response::HTTP_NOT_FOUND, Game::GAME_NOT_FOUND);

        //Let's create a game
        $game_id = uniqid('ttt');
        $game_data = [
            'id' => $game_id,
            'board'   => Game::EMPTY_BOARD,
            'next_player' => '1'
        ];
        DatabaseFixture::createGame($this->entityManager, $game_data);

        //now the game exists, but the player is wrong (it is player 1's turn)
        $body_move = [
            'game_id' => $game_id,
            'player' => 2,
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(Response::HTTP_BAD_REQUEST, sprintf(Game::NOT_PLAYERS_TURN, 2));

        //let's move on a full board
        $game_data['board'] = [
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK,
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK,
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK,
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK,
            Game::PLAYER_1_MARK
        ];
        DatabaseFixture::updateGame($this->entityManager, $game_data);

        $body_move = [
            'game_id' => $game_id,
            'player' => 1,
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(Response::HTTP_BAD_REQUEST, Game::BOARD_FULL);

        //let's move on a position already occupied
        $game_data['board'] = [
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK,
            '',
            '',
            '',
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK,
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK
        ];
        DatabaseFixture::updateGame($this->entityManager, $game_data);

        $occupied_positions = [0, 1, 5, 6, 7, 8];

        foreach($occupied_positions as $position){
            $body_move = [
                'game_id' => $game_id,
                'player' => 1,
                'position' => $position
            ];

            $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
            $this->assertKoResponseApi(Response::HTTP_BAD_REQUEST, Game::POSITION_OCCUPIED);
        }


        //let's move on an already won game
        $game_data['board'] = [
            Game::PLAYER_1_MARK,
            Game::PLAYER_1_MARK,
            Game::PLAYER_1_MARK,
            Game::PLAYER_2_MARK,
            Game::PLAYER_2_MARK,
            '',
            '',
            '',
            ''
        ];
        $game_data['winner'] = 1;
        DatabaseFixture::updateGame($this->entityManager, $game_data);

        $body_move = [
            'game_id' => $game_id,
            'player' => 1,
            'position' => 5
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi(Response::HTTP_BAD_REQUEST, Game::GAME_WON);


    }



}
