<?php

namespace App\HttpClient;
use App\Entity\User as EntityUser;

class Issue
{
    private $id               = 0;
    private $assignedToUserId = 0;
    /** @var \DateTimeImmutable */
    private $updatedOn;
    private $statusId = 0;
    private $subject;
    private $private;
    private EntityUser $user;
    private float $hours = 0.0;

    public function __construct(int $id, int $assignedToUserId, \DateTimeImmutable $updatedOn, int $statusId, string $subject, bool $private)
    {
        $this->id               = $id;
        $this->assignedToUserId = $assignedToUserId;
        $this->updatedOn        = $updatedOn;
        $this->statusId         = $statusId;
        $this->subject          = $subject;
        $this->private          = $private;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAssignedToUserId(): int
    {
        return $this->assignedToUserId;
    }

    public function getUpdatedOn(): \DateTimeImmutable
    {
        return $this->updatedOn;
    }

    public function setStatusId(int $statusId): void
    {
        $this->statusId = $statusId;
    }

    public function getStatusId(): int
    {
        return $this->statusId;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function getUser(): ?EntityUser
    {
        return $this->user;
    }

    public function setUser(EntityUser $user): void
    {
        $this->user = $user;
    }

    public function getHours(): float
    {
        return $this->hours;
    }

    public function setHours(float $hours): void
    {
        $this->hours = $hours;
    }
}
