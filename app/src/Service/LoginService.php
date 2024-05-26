<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginService extends BaseService implements LoginServiceInterface
{

    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;


    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        parent::__construct($entityManager);
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    public function authenticate(string $username, string $password): string
    {
        $user = $this->getEntityManager()->getRepository(User::class)->findOneBy(['username' => $username]);

        if (!$user || $this->passwordHasher->isPasswordValid($user, $password)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Invalid credentials');
        }

        return $this->jwtManager->create($user);
    }
}