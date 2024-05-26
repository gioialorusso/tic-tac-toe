<?php

namespace App\Tests\BaseTestCase;

use App\Tests\Fixture\DatabaseFixture;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

//I was wondering if I can not extend from WebTestCase for this class
//Maybe using a trait? (Probably not - the trait would need to be a class and would need to extend TestCase at least)
abstract class BaseTestCase extends WebTestCase{

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
}