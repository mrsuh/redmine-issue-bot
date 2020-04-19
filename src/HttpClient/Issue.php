<?php

namespace App\HttpClient;

class Issue
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $assignedToUserId;

    /**
     * @var \DateTimeImmutable
     */
    private $updatedOn;

    /**
     * @var int
     */
    private $statusId;

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