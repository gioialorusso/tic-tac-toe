<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game extends BaseEntity
{

    const array EMPTY_BOARD = ['', '', '', '', '', '', '', '', ''];
    const array WINNING_POSITIONS = [
        [0, 1, 2], [3, 4, 5], [6, 7, 8], //these are when you win in a row
        [0, 3, 6], [1, 4, 7], [2, 5, 8], //these are when you win in a column
        [0, 4, 8], [2, 4, 6], //and finally these are when you win in a diagonal
    ];

    const string PLAYER_1 = '1';
    const string PLAYER_2 = '2';
    const string PLAYER_1_MARK = 'X';
    const string PLAYER_2_MARK = 'O';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 16)]
    private string $id;

    #[ORM\Column]
    private array $board = self::EMPTY_BOARD;

    #[ORM\Column(length: 1)]
    //The common convention is that 1 plays first.
    private string $nextPlayer = '1';

    #[ORM\Column(length: 1)]
    private string $winner = '';

    public function __construct()
    {
        $this->id = $this->generateId();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(int $id): static
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

    public function getNextPlayer(): ?string
    {
        return $this->nextPlayer;
    }

    public function setNextPlayer(string $nextPlayer): static
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
            if ($this->board[$winning_position[0]] !== "" && $this->board[$winning_position[0]] === $this->board[$winning_position[1]] && $this->board[$winning_position[1]] === $this->board[$winning_position[2]]) {
                return $this->board[$winning_position[0]] === self::PLAYER_1_MARK ? self::PLAYER_1 : self::PLAYER_2;
            }
        }
        return null;
    }
}
