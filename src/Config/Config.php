<?php

namespace App\Config;

class Config
{

    private $maxDailyHours      = 0;
    private $statusNewId        = 0;
    private $statusInProgressId = 0;

    /**
     * @return int
     */
    public function getMaxDailyHours(): int
    {
        return $this->maxDailyHours;
    }

    /**
     * @param int $maxDailyHours
     */
    public function setMaxDailyHours(int $maxDailyHours): void
    {
        $this->maxDailyHours = $maxDailyHours;
    }

    /**
     * @return int
     */
    public function getStatusNewId(): int
    {
        return $this->statusNewId;
    }

    /**
     * @param int $statusNewId
     */
    public function setStatusNewId(int $statusNewId): void
    {
        $this->statusNewId = $statusNewId;
    }

    /**
     * @return int
     */
    public function getStatusInProgressId(): int
    {
        return $this->statusInProgressId;
    }

    /**
     * @param int $statusInProgressId
     */
    public function setStatusInProgressId(int $statusInProgressId): void
    {
        $this->statusInProgressId = $statusInProgressId;
    }
}