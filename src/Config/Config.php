<?php

namespace App\Config;

class Config
{
    private $maxDailyHours      = 0;
    private $statusNewId        = 0;
    private $statusInProgressId = 0;

    public function getMaxDailyHours(): int
    {
        return $this->maxDailyHours;
    }

    public function setMaxDailyHours(int $maxDailyHours): void
    {
        $this->maxDailyHours = $maxDailyHours;
    }

    public function getStatusNewId(): int
    {
        return $this->statusNewId;
    }

    public function setStatusNewId(int $statusNewId): void
    {
        $this->statusNewId = $statusNewId;
    }

    public function getStatusInProgressId(): int
    {
        return $this->statusInProgressId;
    }

    public function setStatusInProgressId(int $statusInProgressId): void
    {
        $this->statusInProgressId = $statusInProgressId;
    }
}