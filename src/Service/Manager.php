<?php

namespace App\Service;

use App\Entity\Status;
use App\Entity\User;
use App\HttpClient\Issue as HttpIssue;
use App\HttpClient\RedmineHttpClientInterface;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class Manager
{
    private $entityManager;
    private $httpClient;
    private $logger;
    private $statusRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        RedmineHttpClientInterface $httpClient,
        LoggerInterface $logger,
        StatusRepository $statusRepository
    )
    {
        $this->entityManager    = $entityManager;
        $this->httpClient       = $httpClient;
        $this->logger           = $logger;
        $this->statusRepository = $statusRepository;
    }

    /**
     * @param User[] $users
     * @throws \Exception
     */
    public function manage(array $users)
    {
        $inProgressStatus = $this->statusRepository->findOneByType(Status::IN_PROGRESS);
        if ($inProgressStatus === null) {
            throw new \Exception('Can\'t find status "In progress"');
        }

        foreach ($users as $user) {
            $httpIssues = $this->httpClient->getIssuesByUserIdAndStatusId($user->getRedmineId(), $inProgressStatus->getRedmineId());

            $this->logger->debug('Handling user "In Progress" issues', [
                'redmineUserId'    => $user->getRedmineId(),
                'redmineUserLogin' => $user->getRedmineLogin(),
                'issuesCount'      => count($httpIssues)
            ]);

            if (count($httpIssues) > 1) {
                $this->syncIssuesStatuses($user, $httpIssues);
            }
        }
    }

    /**
     * @param HttpIssue[] $issues
     */
    private function syncIssuesStatuses(User $user, array $issues): void
    {
        $newStatus = $this->statusRepository->findOneByType(Status::NEW);
        if ($newStatus === null) {
            throw new \Exception('Can\'t find status "New"');
        }

        $lastAddedId        = 0;
        $lastAddedTimestamp = 0;
        foreach ($issues as $issue) {

            if ($issue->getUpdatedOn()->getTimestamp() > $lastAddedTimestamp) {
                $lastAddedTimestamp = $issue->getUpdatedOn()->getTimestamp();
                $lastAddedId        = $issue->getId();
            }
        }

        foreach ($issues as $issue) {
            if ($issue->getId() === $lastAddedId) {
                continue;
            }

            $this->httpClient->setIssueStatus($issue->getId(), $newStatus->getRedmineId(), $user->getRedmineLogin());
        }
    }
}
