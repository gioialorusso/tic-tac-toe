<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game extends BaseEntity
{

    const string EMPTY_MARK = '';

    const array EMPTY_BOARD = [
        self::EMPTY_MARK,
        self::EMPTY_MARK,
        self::EMPTY_MARK,
        self::EMPTY_MARK,
        self::EMPTY_MARK,
        self::EMPTY_MARK,
        self::EMPTY_MARK,
        self::EMPTY_MARK,
        self::EMPTY_MARK
    ];
    const array WINNING_POSITIONS = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], //these are when you win in a row
        [0, 3, 6], [1, 4, 7], [2, 5, 8], //these are when you win in a column
        [0, 4, 8], [2, 4, 6], //and finally these are when you win in a diagonal
    ];

    const int PLAYER_1 = 1;
    const int PLAYER_2 = 2;
    const string PLAYER_1_MARK = 'X';
    const string PLAYER_2_MARK = 'O';

    //constants for all error messages in functional validation
    const string GAME_NOT_FOUND = 'Game not found';
    const string NOT_PLAYERS_TURN = 'It is not player %s\'s turn.';
    const string BOARD_FULL = 'The board is full.';
    const string POSITION_OCCUPIED = 'This position is already occupied.';
    const string GAME_WON = 'The game is already won.';


    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 16)]
    private string $id;

    #[ORM\Column]
    private array $board = self::EMPTY_BOARD;

    #[ORM\Column(length: 1)]
    //The common convention is that 1 plays first.
    private int $nextPlayer = self::PLAYER_1;

    #[ORM\Column(length: 1)]
    private string $winner = self::EMPTY_MARK;

    public function __construct()
    {
        $this->id = $this->generateId();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getBoard(): array
    {
        return $this->board;
    }

    public function setBoard(array $board): static
    {
        $this->board = $board;

        return $this;
    }

    public function getNextPlayer(): ?int
    {
        return $this->nextPlayer;
    }

    public function setNextPlayer(int $nextPlayer): static
    {
        $this->nextPlayer = $nextPlayer;

        return $this;
    }

    public function getWinner(): string
    {
        return $this->winner;
    }

    public function setWinner(string $winner): void
    {
        $this->winner = $winner;
    }


    public function checkWinner(): ?string
    {
        foreach(self::WINNING_POSITIONS as $winning_position){
            //if all the winning positions are occupied by the same symbol, the player won the game
            //of course the board must not be empty (-‿-")
            if ($this->getBoard()[$winning_position[0]] !== self::EMPTY_MARK && $this->getBoard()[$winning_position[0]] === $this->getBoard()[$winning_position[1]] && $this->getBoard()[$winning_position[1]] === $this->getBoard()[$winning_position[2]]) {
                return $this->getBoard()[$winning_position[0]] === self::PLAYER_1_MARK ? (string)self::PLAYER_1 : (string)self::PLAYER_2;
            }
        }
        return null;
    }

    public function isBoardFull(): bool
    {
        return !in_array(self::EMPTY_MARK, $this->getBoard());
    }

    public function isPositionOccupied(int $position): bool
    {
        return $this->getBoard()[$position] !== self::EMPTY_MARK;
    }

    public function isWon(): bool
    {
        return $this->getWinner() !== self::EMPTY_MARK;
    }

    public function makeMove(int $player, int $position): void
    {

        //let's validate the move
        //wrong player's turn
        if($this->getNextPlayer() != $player){
            $error_message = sprintf(self::NOT_PLAYERS_TURN, $player);
        }
        //move on full board
        if(empty($error_message) && $this->isBoardFull()){
            $error_message = self::BOARD_FULL;
        }
        //move on an already occupied position
        if(empty($error_message) && $this->isPositionOccupied($position)){
            $error_message = self::POSITION_OCCUPIED;
        }
        //move on an already won game
        if(empty($error_message) && $this->isWon()){
            $error_message = self::GAME_WON;
        }

        if(isset($error_message)){
            throw new HttpException(Response::HTTP_BAD_REQUEST, $error_message);
        }


        $this->board[$position] = $player === self::PLAYER_1 ? self::PLAYER_1_MARK : self::PLAYER_2_MARK;
        $this->nextPlayer = $player === self::PLAYER_1 ? self::PLAYER_2 : self::PLAYER_1;
        $winner = $this->checkWinner();
        if(!is_null($winner)){
            $this->winner = $winner;
        }
    }

}
