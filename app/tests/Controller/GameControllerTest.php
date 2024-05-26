<?php

namespace App\Tests\Controller;

use App\Controller\GameController;
use App\Entity\Game;
use App\Tests\ApiTestCase\ApiTestCase;
use App\Tests\Fixture\DatabaseFixture;
use Exception;
use Symfony\Component\HttpFoundation\Response;

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

        //we also need to check if the game is updated in the db
        $query = 'SELECT * FROM game WHERE id = :id';
        $args = ['id' => $game_id];

        $result = DatabaseFixture::query($this->entityManager, $query, $args);
        $this->assertCount(1, $result);
        $this->assertEquals($game_id, $result[0]['id']);
        $this->assertEquals(Game::PLAYER_2, $result[0]['next_player']);
        $this->assertEquals('', $result[0]['winner']);
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
        ], json_decode($result[0]['board'], true));

    }

    public function testMoveAndWin(): void
    {
        //we need a game fixture to test the move api
        //let's assume this is a new game

        $game_id = uniqid('ttt');

        $board = [
            Game::PLAYER_1_MARK,
            Game::PLAYER_1_MARK,
            Game::EMPTY_MARK,
            Game::PLAYER_2_MARK,
            Game::PLAYER_2_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK,
            Game::EMPTY_MARK
        ];

        $game_data = [
            'id' => $game_id,
            'board'   => $board,
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
            'position' => 2
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertOkResponseApi();
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        //we expect that: the game has not a winner, the next player is 2, the board is updated
        $this->assertEquals($game_id, $response['return']['game_id']);
        $this->assertEquals(Game::PLAYER_1, $response['return']['winner']);
        $this->assertEquals(Game::PLAYER_2, $response['return']['next_player']);
        $board[2] = Game::PLAYER_1_MARK;
        $this->assertEquals($board, $response['return']['board']);

        //we also need to check if the game is updated in the db
        $query = 'SELECT * FROM game WHERE id = :id';
        $args = ['id' => $game_id];

        $result = DatabaseFixture::query($this->entityManager, $query, $args);
        $this->assertCount(1, $result);
        $this->assertEquals($game_id, $result[0]['id']);
        $this->assertEquals(Game::PLAYER_2, $result[0]['next_player']);
        $this->assertEquals(Game::PLAYER_1, $result[0]['winner']);
        $this->assertEquals($board, json_decode($result[0]['board'], true));


    }

    private function createGame(): array
    {
        $token = $this->getToken(['username' => 'admin', 'password' => 'password']);
        $this->client->setServerParameter('HTTP_Authorization', sprintf('Bearer %s', $token));
        $this->client->request('POST', '/api/game/start');
        $this->assertOkResponseApi();
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    public function testHorizontalWinGameByPlayer1(){

        $response = $this->createGame();

        //next_player moves in 0
        $response = $this->moveInPosition(0, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 3
        $response = $this->moveInPosition(3, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 1
        $response = $this->moveInPosition(1, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 4
        $response = $this->moveInPosition(4, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 2 and wins
        $response = $this->moveInPosition(2, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board'], $response['return']['next_player']);

        //another move will cause an exception
        $this->moveAndAssertErrorResponse(5, $response['return']['next_player'], $response['return']['game_id'], Response::HTTP_BAD_REQUEST, GameController::GAME_WON);

    }

    public function testHorizontalWinGameByPlayer2(){

        $response = $this->createGame();

        //next_player moves in 0
        $response = $this->moveInPosition(0, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 3
        $response = $this->moveInPosition(3, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 7
        $response = $this->moveInPosition(7, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 4
        $response = $this->moveInPosition(4, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 8
        $response = $this->moveInPosition(8, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 5 and wins
        $response = $this->moveInPosition(5, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board'], $response['return']['next_player']);

        //another move will cause an exception
        $this->moveAndAssertErrorResponse(6, $response['return']['next_player'], $response['return']['game_id'], Response::HTTP_BAD_REQUEST, GameController::GAME_WON);

    }



    public function testVerticalWinGame(){

        $response = $this->createGame();

        //next_player moves in 0
        $response = $this->moveInPosition(0, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 1
        $response = $this->moveInPosition(1, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 3
        $response = $this->moveInPosition(3, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 4
        $response = $this->moveInPosition(4, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 6 and wins
        $response = $this->moveInPosition(6, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board'], $response['return']['next_player']);


        //another move will cause an exception
        $this->moveAndAssertErrorResponse(5, $response['return']['next_player'], $response['return']['game_id'], Response::HTTP_BAD_REQUEST, GameController::GAME_WON);

    }

    public function testDiagonalWinGame(){

        $response = $this->createGame();

        //next_player moves in 0
        $response = $this->moveInPosition(0, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 1
        $response = $this->moveInPosition(1, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 4
        $response = $this->moveInPosition(4, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 5
        $response = $this->moveInPosition(5, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 8 and wins
        $response = $this->moveInPosition(8, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board'], $response['return']['next_player'] );

        //another move will cause an exception
        $this->moveAndAssertErrorResponse(6, $response['return']['next_player'], $response['return']['game_id'], Response::HTTP_BAD_REQUEST, GameController::GAME_WON);

    }

    public function testNoWinner()
    {
        $response = $this->createGame();

        //next_player moves in 0
        $response = $this->moveInPosition(0, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 1
        $response = $this->moveInPosition(1, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 3
        $response = $this->moveInPosition(3, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 6
        $response = $this->moveInPosition(6, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 4
        $response = $this->moveInPosition(4, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 5
        $response = $this->moveInPosition(5, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 2
        $response = $this->moveInPosition(2, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 8
        $response = $this->moveInPosition(8, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //next_player moves in 7
        $response = $this->moveInPosition(7, $response['return']['next_player'], $response['return']['game_id'], $response['return']['board']);

        //another move will cause an exception
        $this->moveAndAssertErrorResponse(8, $response['return']['next_player'], $response['return']['game_id'], Response::HTTP_BAD_REQUEST, GameController::BOARD_FULL);


    }

    private function moveInPosition(int $position, int $player, string $game_id, array $board, string $winner = Game::EMPTY_MARK): array
    {
        $board[$position] = $this->pickNextPlayerMark($player);
        return  $this->moveAndAssertResponse($position, $player, $game_id, $winner, $board);
    }

    private function pickNextPlayerMark(int $next_player): string
    {
        return $next_player === Game::PLAYER_1 ? Game::PLAYER_1_MARK : Game::PLAYER_2_MARK;
    }
    private function moveAndAssertResponse(int $position, int $player, string $game_id,  string $expected_winner, array $expected_board){
        $body_move = [
            'game_id' => $game_id,
            'player' => $player,
            'position' => $position
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertOkResponseApi();
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals($player === Game::PLAYER_1 ? Game::PLAYER_2 : Game::PLAYER_1, $response['return']['next_player']);
        $this->assertEquals($expected_winner, $response['return']['winner']);
        $this->assertEquals($expected_board, $response['return']['board']);

        //added this just for fun, but can be obviously removed
        echo "current board: \n";
        for($i = 0; $i < 3; $i++){
            echo $response['return']['board'][$i * 3] . " | " . $response['return']['board'][$i * 3 + 1] . " | " . $response['return']['board'][$i * 3 + 2] . "\n";
        }

        return $response;

    }

    private function moveAndAssertErrorResponse(int $position, int $player, string $game_id,  int $expected_code, string $expected_msg): void
    {
        $body_move = [
            'game_id' => $game_id,
            'player' => $player,
            'position' => $position
        ];

        $this->client->request('POST', '/api/game/move', [], [], [], json_encode($body_move));
        $this->assertKoResponseApi($expected_code, $expected_msg);


    }

}
