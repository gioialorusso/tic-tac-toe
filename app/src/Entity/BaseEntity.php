<?php

namespace App\Entity;


abstract class BaseEntity
{
    // I am using a generated custom id because:
    // 1. I don't want to use an auto-incremented id because it can be a security issue. (e.g. an attacker can guess the next id or the current id)
    // 2. I don't want to use a UUID because of performance issues. (e.g. UUIDs are 36 characters long and it can be a problem for indexing)
    protected function generateId(): string
    {
        return uniqid('ttt');
    }
}