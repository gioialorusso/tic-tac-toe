<?php

namespace App\Tests\Controller;

use App\Entity\Game;
use App\Tests\ApiTestCase\ApiTestCase;
use App\Tests\Fixture\DatabaseFixture;
use Exception;

class GameControllerTest extends ApiTestCase
{

    /**
     * This function wants to test if all the routes from GameController are only accessible by authorized users.
     */
    public function testOnlyAuthorizedAccesses(): void
    {
        $routes = [
            ['POST', '/api/game/start'],
            ['POST', '/api/game/move'],
        ];

        //calling all the routes without authentication
        foreach($routes as $route){
            $this->client->request($route[0], $route[1]);
            $this->assertUnauthorizedAccess();
        }



        $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3MTY2NTMwMzYsImV4cCI6MTcxNjY1NjYzNiwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQHRpY3RhY3RvZS5jb20ifQ.Qf0ph_9T2DU3Cx9Xz9NIGFTVDfdMxHZ3B8zdWVJb9sFtX1plENwZIKn9lM3B2rNG4nxfMLDDxwcxE5fK83m4Ohfj57UlhT0xNkIJXynigvOacc6IfF_NMZhx7iw6oUAymXkbD3XKWCuJ40cmnxI4OzJ6iFyw-oDw_MkrgU3h1hQ81ut5ONnTKxNH3lpn2iDdVhOgzrx5zKhBp5Pwj99Cap88l8YVmZFkL9ukWyYzcrJ1hfNgS0g-dLhGzd7hVJ8QjnqtyMV94ybBp_LkiQV6k5tgHdL60Fz1zjfJ_itiDNpjKS-R7keeRpAwnwuvgukpRljCBscbgLR5ngk1P_5-zA";
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        foreach($routes as $route){
            $this->client->request($route[0], $route[1]);
            $this->assertExpiredToken();
        }


        //let's obtain a token for a user and let's see that we are not authorized to these routes
        //(we decided to give access only to admins)

        //[TODO] domani vediamo se si riesce a sistemare altrimenti lasciamo perdere
        /*$token = $this->getToken($this->client, ['username' =>'user', 'password' => 'password']);
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        //calling all the routes with user authentication
        foreach($routes as $route){
            $this->client->request($route[0], $route[1]);
            $this->assertUnauthorizedAccess($this->client);
        }*/

    }

    public function testStartGame(): void
    {
        //first of all we need a token to call the start api
        $token = $this->getToken(['username' => 'admin', 'password' => 'password']);
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
        $this->client->request('POST', '/api/game/start');

        //is the response ok?
        $this->assertOkResponseApi();

        //let's see if values are as expected
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotEmpty($response['return']['game_id']);
        $this->assertEquals(Game::PLAYER_1, $response['return']['next_player']);
        $this->assertEquals(Game::EMPTY_MARK, $response['return']['winner']);
        $this->assertEquals(Game::EMPTY_BOARD, $response['return']['board']);


        //great, let's check if the game is in the db
        $game_id = $this->getGameIdFromApiResponse();
        $query = 'SELECT * FROM game WHERE id = :id';
        $args = ['id' => $game_id];

        $result = DatabaseFixture::query($this->entityManager, $query, $args);
        $this->assertCount(1, $result);
        $this->assertEquals($game_id, $result[0]['id']);


    }

    public function testMove(): void
    {
        //we need a game fixture to test the move api
        //let's assume this is a new game

        $game_id = uniqid('ttt');

        $game_data = [
            'id' => $game_id,
            'board'   => Game::EMPTY_BOARD,
            'next_player' => Game::PLAYER_1
        ];

        DatabaseFixture::createGame($this->entityManager, $game_data);

        //let's obtain an admin token to be able to play the game
        $token = $this->getToken(['username' => 'admin', 'password' => 'password']);
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));

        //let's make a move. Player 1 starts, it is his turn. He wants to hit the second row, first column (position 3)
        $body_move = [
            'game_id' => $game_id,
            'player' => Game::PLAYER_1,
            'position' => 3
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertOkResponseApi();
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        //we expect that: the game has not a winner, the next player is 2, the board is updated
        $this->assertEquals($game_id, $response['return']['game_id']);
        $this->assertEquals('', $response['return']['winner']);
        $this->assertEquals(Game::PLAYER_2, $response['return']['next_player']);
        $this->assertEquals([
            Game::EMPTY_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK,
            Game::PLAYER_1_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK
        ], $response['return']['board']);





    }

}
