<?php

namespace App\Tests\Mock;

use App\HttpClient\Issue as HttpIssue;
use App\HttpClient\IssueStatus as HttpIssueStatus;
use App\HttpClient\RedMineHttpClientInterface;
use App\HttpClient\TimeEntry as HttpTimeEntry;
use App\HttpClient\User as HttpUser;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpClient\Response\MockResponse;

class RedmineHttpClientMock implements RedMineHttpClientInterface
{
    /** @var HttpTimeEntry[] */
    public $timeEntries = [];

    /** @var HttpIssue[] */
    public $issues = [];

    /** @var HttpIssueStatus[] */
    public $statuses = [];

    /** @var HttpUser[] */
    public $users = [];

    const userId             = 5;
    const userLogin          = 'userLogin';
    const statusNewId        = 1;
    const statusInProgressId = 2;

    public function __construct()
    {
        $this->init();
    }

    public function init(): void
    {
        $this->timeEntries = [];
        $this->issues      = [];

        $this->statuses = [
            new HttpIssueStatus(self::statusNewId, 'New'),
            new HttpIssueStatus(self::statusInProgressId, 'In Progress'),
        ];

        $this->users[self::userId] = new HttpUser(self::userId, self::userLogin);
    }

    public function addTimeEntry(int $issueId, float $hours, string $userLogin): void
    {
        $this->timeEntries[] = new HttpTimeEntry(self::userId, $issueId, $hours);
    }

    public function setIssueStatus(int $issueId, int $statusId, string $userLogin): void
    {
        foreach ($this->issues as $issue) {
            if ($issue->getId() === $issueId) {
                $issue->setStatusId($statusId);
                break;
            }
        }
    }

    public function getUserById(int $userId): HttpUser
    {
        if (!isset($this->users[$userId])) {
            throw new ServerException(new MockResponse());
        }

        return $this->users[$userId];
    }

    /**
     * @throws \Exception
     * @return HttpIssueStatus[]
     */
    public function getIssueStatuses(): array
    {
        return $this->statuses;
    }

    /**
     * @return HttpIssue[]
     */
    public function getIssuesByUserIdsAndStatusId(array $userIds, int $statusId): array
    {
        $issues = [];
        foreach ($this->issues as $issue) {
            if ($issue->getStatusId() === $statusId && in_array($issue->getAssignedToUserId(), $userIds)) {
                $issues[] = $issue;
            }
        }

        return $issues;
    }

    /**
     * @return HttpTimeEntry[]
     */
    public function getTimeEntriesByUserIdsAndDate(array $userIds, \DateTimeImmutable $date): array
    {
        $timeEntries = [];
        foreach ($this->timeEntries as $timeEntry) {
            if (in_array($timeEntry->getUserId(), $userIds)) {
                $timeEntries[] = $timeEntry;
            }
        }

        return $timeEntries;
    }
}