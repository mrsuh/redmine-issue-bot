<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $redmineId = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $redmineLogin = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $telegramLogin = '';

    public function getRedmineId(): int
    {
        return $this->redmineId;
    }

    public function setRedmineId(int $redmineId): void
    {
        $this->redmineId = $redmineId;
    }

    public function getRedmineLogin(): string
    {
        return $this->redmineLogin;
    }

    public function setRedmineLogin(string $redmineLogin): void
    {
        $this->redmineLogin = $redmineLogin;
    }

    public function getTelegramLogin(): string
    {
        return $this->telegramLogin;
    }

    public function setTelegramLogin(string $telegramLogin): void
    {
        $this->telegramLogin = $telegramLogin;
    }
}
