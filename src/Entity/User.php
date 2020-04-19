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
    private $id = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $login = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $currentTaskId;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $currentTaskStartedAt;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $trackTime = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active = true;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    public function getCurrentTaskId(): ?int
    {
        return $this->currentTaskId;
    }

    public function setCurrentTaskId(?int $currentTaskId): void
    {
        $this->currentTaskId = $currentTaskId;
    }

    public function getCurrentTaskStartedAt(): ?\DateTimeImmutable
    {
        return $this->currentTaskStartedAt;
    }

    public function setCurrentTaskStartedAt(?\DateTimeImmutable $currentTaskStartedAt): void
    {
        $this->currentTaskStartedAt = $currentTaskStartedAt;
    }

    public function isTrackTime(): bool
    {
        return $this->trackTime;
    }

    public function setTrackTime(bool $trackTime): void
    {
        $this->trackTime = $trackTime;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }
}
