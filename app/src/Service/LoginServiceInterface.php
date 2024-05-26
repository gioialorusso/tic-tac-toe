<?php

namespace App\Service;

interface LoginServiceInterface
{
    public function authenticate(string $username, string $password): string;
}