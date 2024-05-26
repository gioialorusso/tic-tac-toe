<?php

namespace App\Tests\Entity;

use App\Entity\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{

    public function testIsBoardFull()
    {
        $game = new Game();
        $this->assertFalse($game->isBoardFull());

        $game->setBoard(['X', 'O', 'X', 'O', 'X', 'O', 'X', 'O', 'X']);

        $this->assertTrue($game->isBoardFull());
    }

    public function testMakeMove()
    {

        $game = new Game();

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

    public function testWinningDetectionAlgorithm(): void
    {
        //empty board - no winning
        $game_data = [
            'board'   => Game::EMPTY_BOARD,
            'next_player' => Game::PLAYER_1
        ];
        $this->istantiateGameAndTestIsWinning($game_data, false);

        for($i = 0; $i < 9; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $this->istantiateGameAndTestIsWinning($game_data, false);
        }

        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i*3] = Game::PLAYER_1_MARK;
            $game_data['board'][($i*3)+1] = Game::PLAYER_1_MARK;        //two in a row
            $this->istantiateGameAndTestIsWinning($game_data, false);
        }

        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i*3] = Game::PLAYER_1_MARK;
            $game_data['board'][($i*3)+1] = Game::PLAYER_1_MARK;        //three in a row!
            $game_data['board'][($i*3)+2] = Game::PLAYER_1_MARK;        //three in a row!
            $this->istantiateGameAndTestIsWinning($game_data, true, Game::PLAYER_1);
        }

        //if I change player mark in the third occurrence, no more win
        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i*3] = Game::PLAYER_1_MARK;
            $game_data['board'][($i*3)+1] = Game::PLAYER_1_MARK;        //three in a row!
            $game_data['board'][($i*3)+2] = Game::PLAYER_2_MARK;        //three in a row!
            $this->istantiateGameAndTestIsWinning($game_data, false);
        }

        //now let's test the columns
        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $game_data['board'][$i+3] = Game::PLAYER_1_MARK;        //two in a column
            $this->istantiateGameAndTestIsWinning($game_data, false);
        }

        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $game_data['board'][$i+3] = Game::PLAYER_1_MARK;        //three in a column!
            $game_data['board'][$i+6] = Game::PLAYER_1_MARK;        //three in a column!
            $this->istantiateGameAndTestIsWinning($game_data, true, Game::PLAYER_1);
        }

        //if I change player mark in the third occurrence, no more win
        for($i = 0; $i < 3; $i++){
            $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
            $game_data['board'][$i] = Game::PLAYER_1_MARK;
            $game_data['board'][$i+3] = Game::PLAYER_1_MARK;        //three in a column!
            $game_data['board'][$i+6] = Game::PLAYER_2_MARK;        //three in a column!
            $this->istantiateGameAndTestIsWinning($game_data, false);
        }

        //and now let's test diagonal wins
        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][0] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $this->istantiateGameAndTestIsWinning($game_data, false);

        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][0] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][8] = Game::PLAYER_1_MARK;        //three in a diagonal!

        $this->istantiateGameAndTestIsWinning($game_data, true, Game::PLAYER_1);

        //if I change player mark in the third occurrence, no more win
        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][0] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][8] = Game::PLAYER_2_MARK;        //three in a diagonal!
        $this->istantiateGameAndTestIsWinning($game_data, false);

        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][2] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $this->istantiateGameAndTestIsWinning($game_data, false);


        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][2] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][6] = Game::PLAYER_1_MARK;        //three in a diagonal!
        $this->istantiateGameAndTestIsWinning($game_data, true, Game::PLAYER_1);

        //if I change player mark in the third occurrence, no more win
        $game_data['board'] = Game::EMPTY_BOARD;        //reset the board
        $game_data['board'][2] = Game::PLAYER_1_MARK;
        $game_data['board'][4] = Game::PLAYER_1_MARK;        //two in a diagonal
        $game_data['board'][6] = Game::PLAYER_2_MARK;        //three in a diagonal!
        $this->istantiateGameAndTestIsWinning($game_data, false);

    }

    private function istantiateGameAndTestIsWinning(array $game_data, bool $winning, string $winner = ''): void
    {

        $game = new Game();
        $game->setBoard($game_data['board']);
        $game->setNextPlayer($game_data['next_player']);

        $winning ? $this->assertEquals($winner, $game->checkWinner()) : $this->assertNull($game->checkWinner());
    }
}