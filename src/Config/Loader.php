<?php

namespace App\Config;

use App\Entity\User;
use App\HttpClient\RedmineHttpClientInterface;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class Loader
{
    private $httpClient;
    private $userRepository;
    private $entityManager;

    public function __construct(RedmineHttpClientInterface $httpClient, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->httpClient     = $httpClient;
        $this->userRepository = $userRepository;
        $this->entityManager  = $entityManager;
    }

    public function load(): Config
    {
        $config = new Config();
        $config->setMaxDailyHours((int)$_ENV['MAX_DAILY_HOURS']);
        $issueStatusNew        = $_ENV['STATUS_NEW_NAME'];
        $issueStatusInProgress = $_ENV['STATUS_IN_PROGRESS_NAME'];

        $httpIssueStatusesGroupedByName = [];
        foreach ($this->httpClient->getIssueStatuses() as $httpIssueStatus) {
            $httpIssueStatusesGroupedByName[$httpIssueStatus->getName()] = $httpIssueStatus->getId();
        }

        if (!isset($httpIssueStatusesGroupedByName[$issueStatusNew])) {
            throw new \Exception(sprintf('Issue status with name "%s" not found', $issueStatusNew));
        }
        $config->setStatusNewId($httpIssueStatusesGroupedByName[$issueStatusNew]);

        if (!isset($httpIssueStatusesGroupedByName[$issueStatusInProgress])) {
            throw new \Exception(sprintf('Issue status with name "%s" not found', $issueStatusInProgress));
        }
        $config->setStatusInProgressId($httpIssueStatusesGroupedByName[$issueStatusInProgress]);

        $trackTimeUserIds       = explode(',', $_ENV['TRACK_TIME_USER_IDS']);
        $syncIssueStatusUserIds = explode(',', $_ENV['SYNC_ISSUES_STATUS_USER_IDS']);
        $userIds                = array_unique(array_merge($syncIssueStatusUserIds, $syncIssueStatusUserIds));

        foreach ($userIds as $userId) {
            $user = $this->userRepository->findOneById($userId);

            if ($user === null) {
                $user = new User($userId);
                $this->entityManager->persist($user);
            }

            $user->setTrackTime(in_array($userId, $trackTimeUserIds));
            $user->setLogin($this->httpClient->getUserById($userId)->getLogin());
            $user->setActive(true);
        }

        $this->entityManager->flush();

        return $config;
    }
}