<?php

namespace App\Tests\Controller;

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
        ];

        //calling all the routes without authentication
        foreach($routes as $route){
            $this->client->request($route[0], $route[1]);
            $this->assertUnauthorizedAccess($this->client);
        }


        //[TODO] let's try with an expired token:
        /*$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3MTY2NTMwMzYsImV4cCI6MTcxNjY1NjYzNiwicm9sZXMiOlsiUk9MRV9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6ImFkbWluQHRpY3RhY3RvZS5jb20ifQ.Qf0ph_9T2DU3Cx9Xz9NIGFTVDfdMxHZ3B8zdWVJb9sFtX1plENwZIKn9lM3B2rNG4nxfMLDDxwcxE5fK83m4Ohfj57UlhT0xNkIJXynigvOacc6IfF_NMZhx7iw6oUAymXkbD3XKWCuJ40cmnxI4OzJ6iFyw-oDw_MkrgU3h1hQ81ut5ONnTKxNH3lpn2iDdVhOgzrx5zKhBp5Pwj99Cap88l8YVmZFkL9ukWyYzcrJ1hfNgS0g-dLhGzd7hVJ8QjnqtyMV94ybBp_LkiQV6k5tgHdL60Fz1zjfJ_itiDNpjKS-R7keeRpAwnwuvgukpRljCBscbgLR5ngk1P_5-zA";
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
        */

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
        $token = $this->getToken($this->client, ['username' => 'admin', 'password' => 'password']);
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
        $this->client->request('POST', '/api/game/start');

        //is the response ok?
        $this->assertOkResponseApi($this->client);

        //great, let's check if the game is in the db
        $game_id = $this->getGameIdFromApiResponse($this->client);
        $query = 'SELECT * FROM game WHERE id = :id';
        $args = ['id' => $game_id];

        $result = DatabaseFixture::query($this->entityManager, $query, $args);
        $this->assertCount(1, $result);
        $this->assertEquals($game_id, $result[0]['id']);


    }

    public function testMove(): void
    {
        //we need a game fixture to test the move api
        DatabaseFixture::createGame();

    }

}
