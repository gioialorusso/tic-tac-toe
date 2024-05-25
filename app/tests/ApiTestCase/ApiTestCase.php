<?php

namespace App\Tests\ApiTestCase;

use App\Tests\Fixture\DatabaseFixture;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTestCase extends WebTestCase
{

    protected KernelBrowser $client;
    protected ?EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        // Get the entity manager
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        // Initialize database setup
        try {
            DatabaseFixture::setupDatabase($this->client);
        }catch (Exception $e){
            $this->fail($e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    protected function assertUnauthorizedAccess(KernelBrowser $client): void
    {
        $this->assertResponseStatusCodeSame(401);
        $response = $client->getResponse();
        $this->assertJson($response->getContent());
        $array_response = json_decode($response->getContent(), true);
        $this->assertEquals('JWT Token not found', $array_response['message']);
    }


    protected function assertOkResponseApi(KernelBrowser $client): void
    {
        $this->assertResponseStatusCodeSame(200);
        $response = $client->getResponse();
        $this->assertJson($response->getContent());
    }


    protected function getToken(KernelBrowser $client, array $user_data): string
    {
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
                'username' => $user_data['username'],
                'password' => $user_data['password']
                ])
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertOkResponseApi($client);
        return $this->getTokenFromApiResponse($client);

    }

    private function getTokenFromApiResponse(KernelBrowser $client): string
    {
        $response = $client->getResponse();
        $response = json_decode($response->getContent(), true);

        return $response['token'];
    }

    protected function getGameIdFromApiResponse(KernelBrowser $client): string
    {
        $response = $client->getResponse();
        $response = json_decode($response->getContent(), true);
        return $response['id'];
    }
    
}