<?php

namespace App\HttpClient;

use App\HttpClient\Issue as HttpIssue;
use App\HttpClient\IssueStatus as HttpIssueStatus;
use App\HttpClient\TimeEntry as HttpTimeEntry;
use App\HttpClient\User as HttpUser;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class RedmineHttpClient implements RedmineHttpClientInterface
{
    private $httpClient;
    private $logger;

    public function __construct(LoggerInterface $logger, string $redmineHttpUrl)
    {
        $explode = explode('?', $redmineHttpUrl);
        parse_str($explode[1], $parsedQuery);
        $token   = $parsedQuery['token'];
        $timeout = $parsedQuery['timeout'] ?? 3;

        $this->httpClient = HttpClient::createForBaseUri($explode[0], [
            'headers' => [
                'X-Redmine-API-Key' => $token,
                'Content-Type'      => 'application/json'
            ],
            'timeout' => $timeout
        ]);

        $this->logger = $logger;
    }

    private function request(string $method, string $url, array $options = []): array
    {
        $this->logger->debug('HTTP Request to redmine', [
            'method'  => $method,
            'url'     => $url,
            'options' => $options,
        ]);

        $response = $this->httpClient->request($method, $url, $options);
        try {

            $data = json_decode($response->getContent(), true);

            if (!is_array($data)) {
                return [];
            }

            return $data;

        } catch (\Exception $e) {
            $this->logger->error('HTTP Request to redmine error', [
                'exception' => $e->getMessage(),
                'method'    => $method,
                'url'       => $url,
                'options'   => $options,
            ]);

            throw $e;
        }
    }

    public function addTimeEntry(int $issueId, float $hours, string $userLogin, \DateTimeImmutable $spentOn): void
    {
        $this->request('POST', 'time_entries.json', [
            'json'    => [
                'time_entry' =>
                    [
                        'issue_id' => $issueId,
                        'hours'    => $hours,
                        'comments' => 'Added by RedmineIssueBot',
                        'spent_at' => $spentOn->format('Y-m-d')
                    ],
                'limit'      => 100//@todo
            ],
            'headers' => [
                'X-Redmine-Switch-User' => $userLogin
            ]
        ]);
    }

    public function setIssueStatus(int $issueId, int $statusId, string $userLogin): void
    {
        $this->request('PUT', sprintf('issues/%d.json', $issueId), [
            'json'    => [
                'issue' =>
                    [
                        'status_id' => $statusId,
                        'notes'     => 'Updated by RedmineIssueBot'
                    ]
            ],
            'headers' => [
                'X-Redmine-Switch-User' => $userLogin
            ]
        ]);
    }

    public function getUserById(int $userId): HttpUser
    {
        $response = $this->request('GET', sprintf('users/%d.json', $userId));

        return new HttpUser((int)$response['user']['id'], $response['user']['login']);
    }

    /**
     * @throws \Exception
     * @return HttpIssueStatus[]
     */
    public function getIssueStatuses(): array
    {
        $response = $this->request('GET', 'issue_statuses.json');

        $issueStatuses = [];
        foreach ($response['issue_statuses'] as $responseIssueStatus) {
            $issueStatuses[] = new HttpIssueStatus((int)$responseIssueStatus['id'], $responseIssueStatus['name']);
        }

        return $issueStatuses;
    }

    /**
     * @throws \Exception
     * @return HttpIssue[]
     */
    public function getIssuesByUserIdAndStatusId(int $userId, int $statusId): array
    {
        $response = $this->request('GET', 'issues.json', [
            'query' => [
                'assigned_to_id' => $userId,
                'status_id'      => $statusId
            ]
        ]);

        $issues = [];
        foreach ($response['issues'] as $responseIssue) {
            $issues[] = new HttpIssue(
                (int)$responseIssue['id'],
                (int)$responseIssue['assigned_to']['id'],
                \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $responseIssue['updated_on']),
                (int)$responseIssue['status']['id'],
                $responseIssue['subject'],
                (bool)$responseIssue['is_private']
            );
        }

        return $issues;
    }

    /**
     * @throws \Exception
     * @return HttpIssue[]
     */
    public function getIssuesByUserId(int $userId): array
    {
        $response = $this->request('GET', 'issues.json', [
            'query' => [
                'assigned_to_id' => $userId
            ]
        ]);

        $issues = [];
        foreach ($response['issues'] as $responseIssue) {
            $issues[] = new HttpIssue(
                (int)$responseIssue['id'],
                (int)$responseIssue['assigned_to']['id'],
                \DateTimeImmutable::createFromFormat(\DateTime::ATOM, $responseIssue['updated_on']),
                (int)$responseIssue['status']['id'],
                $responseIssue['subject'],
                (bool)$responseIssue['is_private']
            );
        }

        return $issues;
    }

    /**
     * @throws \Exception
     * @return HttpTimeEntry[]
     */
    public function getTimeEntriesByUserIdsAndDate(array $userIds, \DateTimeImmutable $date): array
    {
        $response = $this->request('GET', 'time_entries.json', [
            'query' => [
                'user_id'  => implode(',', $userIds),
                'spent_on' => $date->format('Y-m-d'),
                'limit'    => 100//@todo
            ]
        ]);

        $timeEntries = [];
        foreach ($response['time_entries'] as $responseTimeEntry) {
            $timeEntries[] = new HttpTimeEntry($responseTimeEntry['user']['id'], $responseTimeEntry['issue']['id'], $responseTimeEntry['hours']);
        }

        return $timeEntries;
    }

    /**
     * @throws \Exception
     * @return HttpTimeEntry[]
     */
    public function getTimeEntriesByUserIdAndIssueId(int $userId, int $issueId): array
    {
        $response = $this->request('GET', 'time_entries.json', [
            'query' => [
                'user_id'  => $userId,
                'issue_id' => $issueId,
                'limit'    => 100//@todo
            ]
        ]);

        $timeEntries = [];
        foreach ($response['time_entries'] as $responseTimeEntry) {
            $timeEntries[] = new HttpTimeEntry($responseTimeEntry['user']['id'], $responseTimeEntry['issue']['id'], $responseTimeEntry['hours']);
        }

        return $timeEntries;
    }
}
