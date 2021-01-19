<?php

namespace App\Command\Issue;

use App\Entity\User;
use App\HttpClient\Issue;
use App\HttpClient\RedmineHttpClient;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\BotApi;

class NotifyCommand extends Command
{
    protected static $defaultName = 'issue:notify';

    private        $redmineHttpClient;
    private        $userRepository;
    private        $statusRepository;
    private BotApi $botApi;
    private string $telegramChatId;
    private string $redmineBaseUrl;

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE);
    }

    public function __construct(RedmineHttpClient $redmineHttpClient, UserRepository $userRepository, StatusRepository $statusRepository, BotApi $botApi, string $telegramChatId, string $redmineHttpUrl)
    {
        $this->redmineHttpClient = $redmineHttpClient;
        $this->userRepository    = $userRepository;
        $this->statusRepository  = $statusRepository;
        $this->botApi            = $botApi;
        $this->telegramChatId    = $telegramChatId;
        $parse                   = parse_url($redmineHttpUrl);
        $this->redmineBaseUrl    = "{$parse['scheme']}://{$parse['host']}";
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statuses = [];
        foreach ($this->statusRepository->findForReview() as $status) {
            $statuses[$status->getRedmineId()] = $status->getRedmineName();
        }

        $issues = [];
        foreach ($this->userRepository->findAll() as $user) {
            foreach ($this->redmineHttpClient->getIssuesByUserId($user->getRedmineId()) as $issue) {

                if (!in_array($issue->getStatusId(), array_keys($statuses))) {
                    continue;
                }

                if ($issue->isPrivate()) {
                    continue;
                }

                $issues[] = ['issue' => $issue, 'user' => $user];
            }
        }

        usort($issues, function ($a, $b): int {
            /** @var Issue $issueA */
            $issueA = $a['issue'];

            /** @var Issue $issueB */
            $issueB = $b['issue'];

            /** @var User $userA */
            $userA = $a['user'];

            /** @var User $userB */
            $userB = $b['user'];

            return $issueA->getStatusId() - $issueB->getStatusId() ?: strcmp($userA->getRedmineLogin(), $userB->getRedmineLogin());
        });

        $message         = '';
        $currentStatusId = 0;
        foreach ($issues as $data) {
            /** @var Issue $issue */
            $issue = $data['issue'];

            /** @var User $user */
            $user = $data['user'];

            if ($currentStatusId !== $issue->getStatusId()) {
                $message         .= "\n{$statuses[$issue->getStatusId()]}:\n";
                $currentStatusId = $issue->getStatusId();
            }

            $username = $user->getRedmineLogin();
            if (!empty($user->getTelegramLogin())) {
                $username = "@{$user->getTelegramLogin()}";
            }

            $message .= "+ {$username} - {$this->redmineBaseUrl}/issues/{$issue->getId()} {$issue->getSubject()}\n";
        }

        echo $message;

        if ($input->getOption('dry-run')) {
            return 0;
        }

        $this->botApi->sendMessage($this->telegramChatId, trim($message));

        return 0;
    }
}
