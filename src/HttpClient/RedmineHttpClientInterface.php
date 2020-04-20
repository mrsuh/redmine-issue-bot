<?php

namespace App\HttpClient;

use App\HttpClient\Issue as HttpIssue;
use App\HttpClient\IssueStatus as HttpIssueStatus;
use App\HttpClient\TimeEntry as HttpTimeEntry;
use App\HttpClient\User as HttpUser;

interface RedmineHttpClientInterface
{
    public function addTimeEntry(int $issueId, float $hours, string $userLogin, \DateTimeImmutable $spentOn): void;

    public function setIssueStatus(int $issueId, int $statusId, string $userLogin): void;

    public function getUserById(int $userId): HttpUser;

    /**
     * @return HttpIssueStatus[]
     */
    public function getIssueStatuses(): array;

    /**
     * @return HttpIssue[]
     */
    public function getIssuesByUserIdAndStatusId(int $userId, int $statusId): array;

    /**
     * @return HttpTimeEntry[]
     */
    public function getTimeEntriesByUserIdsAndDate(array $userIds, \DateTimeImmutable $date): array;
}