<?php

namespace App\HttpClient;

class User
{
    private $id    = 0;
    private $login = '';

    public function __construct(int $id, string $login)
    {
        $this->id    = $id;
        $this->login = $login;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }
}