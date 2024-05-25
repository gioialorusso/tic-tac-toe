<?php

namespace App\EventListener;

use App\ApiResponse\ApiResponse;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationFailureListener
{
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {
        $response = ApiResponse::createKOResponse(Response::HTTP_UNAUTHORIZED, [], $event->getException()->getMessage());
        $event->setResponse($response);
    }
}