<?php

namespace App\Tests\Entity;

use App\Entity\Game;
use App\Tests\BaseTestCase\BaseTestCase;
use App\Tests\Fixture\DatabaseFixture;

class GameTest extends BaseTestCase
{
    public function testWinningDetectionAlgorithm(): void
    {
        //empty board - no winning
        $game_data = [
            'board'   => Game::EMPTY_BOARD,
            'next_player' => Game::PLAYER_1
        ];
        $this->createGameAndTestIsWinning($game_data, false);

        for($i = 0; $i < 9; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $this->createGameAndTestIsWinning($game_data, false);
        }

        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i*3] = Game::PLAYER_1_MARK;
            $game_data['board'][($i*3)+1] = Game::PLAYER_1_MARK;        //two in a row
            $this->createGameAndTestIsWinning($game_data, false);
        }

        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i*3] = Game::PLAYER_1_MARK;
            $game_data['board'][($i*3)+1] = Game::PLAYER_1_MARK;        //three in a row!
            $game_data['board'][($i*3)+2] = Game::PLAYER_1_MARK;        //three in a row!
            $this->createGameAndTestIsWinning($game_data, true, Game::PLAYER_1);
        }

        //if I change player mark in the third occurrence, no more win
        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i*3] = Game::PLAYER_1_MARK;
            $game_data['board'][($i*3)+1] = Game::PLAYER_1_MARK;        //three in a row!
            $game_data['board'][($i*3)+2] = Game::PLAYER_2_MARK;        //three in a row!
            $this->createGameAndTestIsWinning($game_data, false);
        }

        //now let's test the columns
        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $game_data['board'][$i+3] = Game::PLAYER_1_MARK;        //two in a column
            $this->createGameAndTestIsWinning($game_data, false);
        }

        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $game_data['board'][$i+3] = Game::PLAYER_1_MARK;        //three in a column!
            $game_data['board'][$i+6] = Game::PLAYER_1_MARK;        //three in a column!
            $this->createGameAndTestIsWinning($game_data, true, Game::PLAYER_1);
        }

        //if I change player mark in the third occurrence, no more win
        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $game_data['board'][$i+3] = Game::PLAYER_1_MARK;        //three in a column!
            $game_data['board'][$i+6] = Game::PLAYER_2_MARK;        //three in a column!
            $this->createGameAndTestIsWinning($game_data, false);
        }

        //and now let's test diagonal wins
        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][0] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $this->createGameAndTestIsWinning($game_data, false);

        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][0] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][8] = Game::PLAYER_1_MARK;        //three in a diagonal!

        $this->createGameAndTestIsWinning($game_data, true, Game::PLAYER_1);

        //if I change player mark in the third occurrence, no more win
        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][0] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][8] = Game::PLAYER_2_MARK;        //three in a diagonal!
        $this->createGameAndTestIsWinning($game_data, false);

        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][2] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $this->createGameAndTestIsWinning($game_data, false);


        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][2] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][6] = Game::PLAYER_1_MARK;        //three in a diagonal!
        $this->createGameAndTestIsWinning($game_data, true, Game::PLAYER_1);

        //if I change player mark in the third occurrence, no more win
        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][2] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][6] = Game::PLAYER_2_MARK;        //three in a diagonal!
        $this->createGameAndTestIsWinning($game_data, false);

    }

    private function createGameAndTestIsWinning(array $game_data, bool $winning, string $winner = '')
    {
        $game_id = uniqid('ttt');
        $game_data['id'] = $game_id;
        DatabaseFixture::createGame($this->entityManager, $game_data);
        $game = $this->entityManager->getRepository(Game::class)->find($game_id);
        $winning ? $this->assertEquals($winner, $game->checkWinner()) : $this->assertNull($game->checkWinner());
    }

    public function testMakeMove()
    {

        $game_id = uniqid('ttt');
        $game_data = [
            'id' => $game_id,
            'board'   => Game::EMPTY_BOARD,
            'next_player' => Game::PLAYER_1
        ];
        DatabaseFixture::createGame($this->entityManager, $game_data);

        $game = $this->entityManager->getRepository(Game::class)->find($game_id);

        $game->makeMove(Game::PLAYER_1, 0);
        $this->assertEquals(Game::PLAYER_1_MARK, $game->getBoard()[0]);
        $this->assertEquals(Game::PLAYER_2, $game->getNextPlayer());
        $this->assertEquals(Game::EMPTY_MARK, $game->getWinner());

        $game->makeMove(Game::PLAYER_2, 1);
        $this->assertEquals(Game::PLAYER_2_MARK, $game->getBoard()[1]);
        $this->assertEquals(Game::PLAYER_1, $game->getNextPlayer());
        $this->assertEquals(Game::EMPTY_MARK, $game->getWinner());


        $game->makeMove(Game::PLAYER_1, 2);
        $this->assertEquals(Game::PLAYER_1_MARK, $game->getBoard()[2]);
        $this->assertEquals(Game::PLAYER_2, $game->getNextPlayer());
        $this->assertEquals(Game::EMPTY_MARK, $game->getWinner());


        $game->makeMove(Game::PLAYER_2, 3);
        $this->assertEquals(Game::PLAYER_2_MARK, $game->getBoard()[3]);
        $this->assertEquals(Game::PLAYER_1, $game->getNextPlayer());
        $this->assertEquals(Game::EMPTY_MARK, $game->getWinner());


        $game->makeMove(Game::PLAYER_1, 4);
        $this->assertEquals(Game::PLAYER_1_MARK, $game->getBoard()[4]);
        $this->assertEquals(Game::PLAYER_2, $game->getNextPlayer());
        $this->assertEquals(Game::EMPTY_MARK, $game->getWinner());


        $game->makeMove(Game::PLAYER_2, 5);
        $this->assertEquals(Game::PLAYER_2_MARK, $game->getBoard()[5]);
        $this->assertEquals(Game::PLAYER_1, $game->getNextPlayer());
        $this->assertEquals(Game::EMPTY_MARK, $game->getWinner());


        $game->makeMove(Game::PLAYER_1, 6);
        $this->assertEquals(Game::PLAYER_1_MARK, $game->getBoard()[6]);
        $this->assertEquals(Game::PLAYER_2, $game->getNextPlayer());
        $this->assertEquals(Game::PLAYER_1, $game->getWinner());



    }
}