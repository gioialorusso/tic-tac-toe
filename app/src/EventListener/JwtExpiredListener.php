<?php

namespace App\EventListener;

use App\ApiResponse\ApiResponse;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Response;

class JwtExpiredListener
{
    public function onJwtExpiredResponse(AuthenticationFailureEvent $event): void
    {
        //for some strange reason in this event I can't find the error message in the exception
        $response = ApiResponse::createKOResponse(Response::HTTP_UNAUTHORIZED, [], 'Expired JWT Token');
        $event->setResponse($response);
    }
}