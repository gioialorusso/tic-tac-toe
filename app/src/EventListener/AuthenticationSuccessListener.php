<?php

namespace App\EventListener;

use App\ApiResponse\ApiResponse;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class AuthenticationSuccessListener{

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {
        $event->setData([
            ApiResponse::getOKPayload($event->getData())
        ]);
    }

}

