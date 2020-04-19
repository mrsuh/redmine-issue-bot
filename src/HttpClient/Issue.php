<?php

namespace App\HttpClient;

class Issue
{
    private $id               = 0;
    private $assignedToUserId = 0;
    /** @var \DateTimeImmutable */
    private $updatedOn;
    private $statusId = 0;

    public function __construct(int $id, int $assignedToUserId, \DateTimeImmutable $updatedOn, int $statusId)
    {
        $this->id               = $id;
        $this->assignedToUserId = $assignedToUserId;
        $this->updatedOn        = $updatedOn;
        $this->statusId         = $statusId;
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
}