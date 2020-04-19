<?php

namespace App\HttpClient;

class TimeEntry
{
    private $userId  = 0;
    private $issueId = 0;
    private $hours   = 0.0;

    public function __construct(int $userId, int $issueId, float $hours)
    {
        $this->userId  = $userId;
        $this->issueId = $issueId;
        $this->hours   = $hours;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getIssueId(): int
    {
        return $this->issueId;
    }

    public function getHours(): float
    {
        return $this->hours;
    }
}