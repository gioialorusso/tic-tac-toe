<?php


namespace App\Tests\Common;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;

class DatabaseFixture extends KernelTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public static function setupDatabase(KernelBrowser $client): void
    {

        $application = new Application($client->getKernel());
        $application->setAutoExit(false);

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:drop',
            '--force' => true,
            '--env' => 'test',
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:database:create',
            '--env' => 'test',
        ]));

        $application->run(new ArrayInput([
            'command' => 'doctrine:schema:create',
            '--env' => 'test',
        ]));

        // Load fixed data fixtures if needed
        $application->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--env' => 'test',
            '--no-interaction' => true,
        ]));
    }

    public static function query(EntityManagerInterface $entityManager, string $query, array $args = []): array
    {
        $connection = $entityManager->getConnection();
        $result = $connection->executeQuery($query, $args);
        return $result->fetchAllAssociative();

    }

    public static function createGame(EntityManagerInterface $entityManager, array $game_data): void
    {
        $query = "INSERT INTO game (id, board, next_player, winner) VALUES (:id, :board, :next_player, :winner)";
        $connection = $entityManager->getConnection();
        $args = [
            'id' => $game_data['id'],
            'board' => json_encode($game_data['board']),
            'next_player' => $game_data['next_player'],
            'winner' => $game_data['winner'] ?? ''
        ];
        $connection->executeQuery($query, $args);
    }

    public static function updateGame(EntityManagerInterface $entityManager, array $game_data): void
    {
        $query = "UPDATE game SET board = :board, next_player = :next_player, winner = :winner WHERE id = :id";
        $connection = $entityManager->getConnection();
        $args = [
            'id' => $game_data['id'],
            'board' => json_encode($game_data['board']),
            'next_player' => $game_data['next_player'],
            'winner' => $game_data['winner'] ?? ''
        ];
        $connection->executeQuery($query, $args);
    }

}
