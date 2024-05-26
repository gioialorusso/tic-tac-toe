<?php

namespace App\Controller;

use App\Service\LoginServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiLoginController extends AbstractController
{

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function index(Request $request, LoginServiceInterface $loginService): JsonResponse
    {
        $token = $loginService->authenticate($request->request->get('username'), $request->request->get('password'));
        return new JsonResponse(['token' => $token]);
    }
}