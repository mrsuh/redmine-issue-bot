<?php

namespace App\Service;

use App\Config\Config;
use App\Entity\User;
use App\HttpClient\Issue as HttpIssue;
use App\HttpClient\RedmineHttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class Manager
{
    private $entityManager;
    private $httpClient;
    private $logger;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        EntityManagerInterface $entityManager,
        RedmineHttpClientInterface $httpClient,
        LoggerInterface $logger
    )
    {
        $this->entityManager = $entityManager;
        $this->httpClient    = $httpClient;
        $this->logger        = $logger;
    }

    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * @param User[] $users
     * @throws \Exception
     */
    public function manage(array $users)
    {
        foreach ($users as $user) {
            $httpIssues = $this->httpClient->getIssuesByUserIdAndStatusId($user->getId(), $this->config->getStatusInProgressId());

            $this->logger->debug('Handling user issues', ['userId' => $user->getId(), 'userLogin' => $user->getLogin(), 'issuesCount' => count($httpIssues)]);

            $this->handleUserIssues($user, $httpIssues);
        }

        $this->handleMaxHours($users);

        $this->handleDailyIssues($users);
    }

    private function handleUserIssues(User $user, $issues)
    {
        $totalCountIssues = count($issues);

        switch (true) {
            case $totalCountIssues === 0:
                $this->handleNoOneInProgressIssues($user);
                break;
            case $totalCountIssues === 1:
                $this->handleOneInProgressIssue($user, current($issues));
                break;
            case $totalCountIssues > 1:
                $this->handleMultipleInProgressIssues($user, $issues);
                break;
        }
    }

    private function handleNoOneInProgressIssues(User $user)
    {
        $this->logger->info('No issues in progress', ['userId' => $user->getId(), 'userLogin' => $user->getLogin()]);

        if ($user->getCurrentTaskId() === null) {
            return;
        }

        $this->addCurrentTimeEntry($user);

        $user->setCurrentTaskId(null);
        $user->setCurrentTaskStartedAt(null);

        $this->entityManager->flush();
    }

    private function handleOneInProgressIssue(User $user, HttpIssue $issue)
    {
        $this->logger->info('One issue in progress', ['userId' => $user->getId(), 'userLogin' => $user->getLogin()]);

        if ($user->getCurrentTaskId() === null) {
            $user->setCurrentTaskId($issue->getId());
            $user->setCurrentTaskStartedAt(new \DateTimeImmutable());

            $this->entityManager->flush();

            return;
        }

        if ($issue->getId() !== $user->getCurrentTaskId()) {

            $this->addCurrentTimeEntry($user);

            $this->setIssueStatus($user, $user->getCurrentTaskId(), $this->config->getStatusNewId());

            $user->setCurrentTaskId($issue->getId());
            $user->setCurrentTaskStartedAt(new \DateTimeImmutable());

            $this->entityManager->flush();

            return;
        }

        if ($issue->getId() === $user->getCurrentTaskId()) {
            $currentIssueHours = $this->roundTime(new \DateTimeImmutable(), $user->getCurrentTaskStartedAt());

            $this->logger->info('Same issue in progress', [
                'issueId' => $user->getCurrentTaskId(),
                'hours'   => $currentIssueHours
            ]);
        }
    }

    /**
     * @param User        $user
     * @param HttpIssue[] $issues
     */
    private function handleMultipleInProgressIssues(User $user, array $issues): void
    {
        $this->logger->info('Multiple issues in progress', ['userId' => $user->getId(), 'userLogin' => $user->getLogin()]);

        $lastAddedId        = 0;
        $lastAddedTimestamp = 0;
        foreach ($issues as $issue) {

            if ($issue->getUpdatedOn()->getTimestamp() > $lastAddedTimestamp) {
                $lastAddedTimestamp = $issue->getUpdatedOn()->getTimestamp();
                $lastAddedId        = $issue->getId();
            }
        }

        if ($user->getCurrentTaskId() !== null && $user->getCurrentTaskId() !== $lastAddedId) {
            $this->addCurrentTimeEntry($user);
        }

        $user->setCurrentTaskId($lastAddedId);
        $user->setCurrentTaskStartedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        foreach ($issues as $issue) {
            if ($issue->getId() === $lastAddedId) {
                continue;
            }

            $this->setIssueStatus($user, $issue->getId(), $this->config->getStatusNewId());
        }
    }

    /**
     * @param User[] $users
     * @throws \Exception
     */
    private function handleMaxHours(array $users)
    {
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user->getId();
        }

        $httpTimeEntries                = $this->httpClient->getTimeEntriesByUserIdsAndDate($userIds, new \DateTimeImmutable());
        $httpTimeEntriesGroupedByUserId = [];
        foreach ($httpTimeEntries as $httpTimeEntry) {
            if (!array_key_exists($httpTimeEntry->getUserId(), $httpTimeEntriesGroupedByUserId)) {
                $httpTimeEntriesGroupedByUserId[$httpTimeEntry->getUserId()] = 0.0;
            }
            $httpTimeEntriesGroupedByUserId[$httpTimeEntry->getUserId()] += $httpTimeEntry->getHours();
        }

        foreach ($users as $user) {
            $userId = $user->getId();

            $this->logger->debug('Handling user issues max hours', ['userId' => $user->getId(), 'userLogin' => $user->getLogin()]);

            if ($user->getCurrentTaskId() === null) {
                continue;
            }

            $totalUserHours = $this->roundTime(new \DateTimeImmutable(), $user->getCurrentTaskStartedAt());
            if (isset($httpTimeEntriesGroupedByUserId[$userId])) {
                $totalUserHours += $httpTimeEntriesGroupedByUserId[$userId];
            }

            if ($totalUserHours >= $this->config->getMaxDailyHours()) {
                $this->addCurrentTimeEntry($user);

                $this->setIssueStatus($user, $user->getCurrentTaskId(), $this->config->getStatusNewId());

                $user->setCurrentTaskId(null);
                $user->setCurrentTaskStartedAt(null);

                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param User[] $users
     * @throws \Exception
     */
    private function handleDailyIssues(array $users)
    {
        $date = new \DateTimeImmutable();
        foreach ($users as $user) {

            $this->logger->debug('Handling daily user issues', ['userId' => $user->getId(), 'userLogin' => $user->getLogin()]);

            if ($user->getCurrentTaskId() === null) {
                continue;
            }

            if ($user->getCurrentTaskStartedAt()->format('Y-m-d') === $date->format('Y-m-d')) {
                continue;
            }

            $this->setIssueStatus($user, $user->getCurrentTaskId(), $this->config->getStatusNewId());
            $this->addCurrentTimeEntry($user);

            $user->setCurrentTaskId(null);
            $user->setCurrentTaskStartedAt(null);
            $this->entityManager->flush();
        }
    }

    private function addCurrentTimeEntry(User $user)
    {
        if (!$user->isTrackTime()) {
            return;
        }

        $time = $this->roundTime(new \DateTimeImmutable(), $user->getCurrentTaskStartedAt());
        if ($time === 0.0) {
            return;
        }

        $this->httpClient->addTimeEntry(
            $user->getCurrentTaskId(),
            $time,
            $user->getLogin(),
            $user->getCurrentTaskStartedAt()
        );
    }

    private function setIssueStatus(User $user, int $issueId, int $statusId)
    {
        $this->httpClient->setIssueStatus($issueId, $statusId, $user->getLogin());
    }

    private function roundTime(\DateTimeImmutable $from, \DateTimeImmutable $to): float
    {
        return round(($from->getTimestamp() - $to->getTimestamp()) / 60 / 60, 4);
    }
}
