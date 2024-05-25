<?php

namespace App\Controller;

use App\ApiResponse\ApiResponse;
use App\Entity\Game;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class GameController extends AbstractController
{
    #[Route('/api/game/start', name: 'start_game', methods: ['POST'])]
    public function start(EntityManagerInterface $entityManager): JsonResponse
    {
        $game = new Game();
        $entityManager->persist($game);
        $entityManager->flush();

        $return_data = [
            'id' => $game->getId(),
            'board' => $game->getBoard(),
            'currentPlayer' => $game->getCurrentPlayer()
        ];

        return ApiResponse::createOKResponse($return_data);
    }

    #[Route('/api/game/move', name: 'make_move', methods: ['POST'])]
    public function move(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {

    }

    /*#[Route('/test', name: 'test', methods: ['GET'])]
    public function test(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher){
        //$game = new Game();
        //return $this->json(['id' => $game->getId()]);
        //$entityManager->getConnection()->connect();
        //$connected = $entityManager->getConnection()->isConnected();
        //return $this->json(['connected' => $connected]);
        $user = new User();
        $user->setUsername('admin@tictactoe.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN']);
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->json(['id' => $user->getId()]);
    }*/
}
