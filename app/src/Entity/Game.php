<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game extends BaseEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 16)]
    private string $id;

    #[ORM\Column]
    private array $board = [['', '', ''], ['', '', ''], ['', '', '']];

    #[ORM\Column(length: 1)]
    //The common convention is that X plays first.
    private string $currentPlayer = 'X';

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

    public function getCurrentPlayer(): ?string
    {
        return $this->currentPlayer;
    }

    public function setCurrentPlayer(string $currentPlayer): static
    {
        $this->currentPlayer = $currentPlayer;

        return $this;
    }
}
