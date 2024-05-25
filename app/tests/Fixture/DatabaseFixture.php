<?php


namespace App\Tests\Fixture;

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
        $query = "INSERT INTO game (id, board, current_player) VALUES (:id, :board, :current_player)";
        $connection = $entityManager->getConnection();
        $args = [
            'id' => $game_data['id'],
            'board' => $game_data['board'],
            'current_player' => $game_data['current_player']
        ];
        $connection->executeQuery($query, $args);
    }

}
