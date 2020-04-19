<?php

namespace App\Tests\Unit;

use App\HttpClient\Issue as HttpIssue;
use App\HttpClient\TimeEntry as HttpTimeEntry;

class TestSet
{
    /** @var int|null */
    public $userCurrentTaskId = null;

    /** @var \DateTimeImmutable|null */
    public $userCurrentTaskStartedAt = null;

    /** @var HttpIssue[] */
    public $issues = [];

    /** @var HttpTimeEntry */
    public $timeEntry = null;

    public function __construct(array $issues = [], ?int $userCurrentTaskId = null, ?\DateTimeImmutable $userCurrentTaskStartedAt = null, ?HttpTimeEntry $timeEntry = null)
    {
        $this->issues                   = $issues;
        $this->userCurrentTaskId        = $userCurrentTaskId;
        $this->userCurrentTaskStartedAt = $userCurrentTaskStartedAt;
        $this->timeEntry                = $timeEntry;
    }

    public function getIssueById(int $id): ?HttpIssue
    {
        foreach ($this->issues as $issue) {
            if ($issue->getId() === $id) {
                return $issue;
            }
        }

        return null;
    }
}