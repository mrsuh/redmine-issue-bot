<?php

namespace App\Command\Issue;

use App\Entity\Status;
use App\HttpClient\Issue;
use App\HttpClient\RedmineHttpClient;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\BotApi;
use Twig\Environment;

class NotifyCommand extends Command
{
    protected static $defaultName = 'issue:notify';

    private             $redmineHttpClient;
    private             $userRepository;
    private             $statusRepository;
    private BotApi      $botApi;
    private Environment $twig;
    private string      $telegramChatId;
    private string      $redmineBaseUrl;

    protected function configure(): void
    {
        $this->addOption('dry-run', null, InputOption::VALUE_NONE);
    }

    public function __construct(
        RedmineHttpClient $redmineHttpClient,
        UserRepository $userRepository,
        StatusRepository $statusRepository,
        BotApi $botApi,
        Environment $twig,
        string $telegramChatId,
        string $redmineHttpUrl
    )
    {
        $this->redmineHttpClient = $redmineHttpClient;
        $this->userRepository    = $userRepository;
        $this->statusRepository  = $statusRepository;
        $this->twig              = $twig;
        $this->botApi            = $botApi;
        $this->telegramChatId    = $telegramChatId;
        $parse                   = parse_url($redmineHttpUrl);
        $this->redmineBaseUrl    = "{$parse['scheme']}://{$parse['host']}";
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $previousBusinessDay = $this->getPreviousBusinessDay();
        $statuses            = [];
        foreach ($this->statusRepository->findForReview() as $status) {
            $statuses[$status->getRedmineId()] = $status->getRedmineName();
        }

        $inProgressStatus = $this->statusRepository->findOneByType(Status::IN_PROGRESS);
        if ($inProgressStatus === null) {
            throw new \Exception();
        }

        $usersWithoutInProgressIssues    = [];
        $timeLessUsers    = [];
        $issues           = [];
        $inProgressIssues = [];
        foreach ($this->userRepository->findAll() as $user) {
            $timeEntries = $this->redmineHttpClient->getTimeEntriesByUserIdsAndDate([$user->getRedmineId()], $previousBusinessDay);
            if (count($timeEntries) === 0) {
                $timeLessUsers[] = $user;
            }

            $hasInProgressIssue = false;
            foreach ($this->redmineHttpClient->getIssuesByUserId($user->getRedmineId()) as $issue) {
                $issue->setUser($user);

                if ($inProgressStatus->getRedmineId() === $issue->getStatusId()) {

                    $timeEntries = $this->redmineHttpClient->getTimeEntriesByUserIdAndIssueId($user->getRedmineId(), $issue->getId());
                    $hours       = 0.0;
                    foreach ($timeEntries as $timeEntry) {
                        $hours += $timeEntry->getHours();
                    }
                    $issue->setHours($hours);

                    $inProgressIssues[] = $issue;
                    $hasInProgressIssue = true;
                }

                if (!in_array($issue->getStatusId(), array_keys($statuses))) {
                    continue;
                }

                if ($issue->isPrivate()) {
                    continue;
                }

                $issues[] = $issue;
            }
            if(!$hasInProgressIssue) {
                $usersWithoutInProgressIssues[] = $user;
            }
        }

        usort($issues, function (Issue $a, Issue $b): int {
            return $a->getStatusId() - $b->getStatusId() ?: strcmp($a->getUser()->getRedmineLogin(), $b->getUser()->getRedmineLogin());
        });

        $issuesByStatuses = [];
        foreach ($issues as $issue) {
            $status = $statuses[$issue->getStatusId()];

            if (!array_key_exists($status, $issuesByStatuses)) {
                $issuesByStatuses[$status] = [];
            }

            $issuesByStatuses[$status][] = $issue;
        }

        $message = $this->twig->render('notification.html.twig', [
            'redmineBaseUrl'      => $this->redmineBaseUrl,
            'issuesByStatuses'    => $issuesByStatuses,
            'timeLessUsers'       => $timeLessUsers,
            'previousBusinessDay' => $previousBusinessDay,
            'inProgressIssues'    => $inProgressIssues,
            'usersWithoutInProgressIssues'    => $usersWithoutInProgressIssues,
        ]);

        if ($input->getOption('dry-run')) {
            $output->writeln($message);

            return 0;
        }

        $this->botApi->sendMessage($this->telegramChatId, trim($message), 'html');

        return 0;
    }

    private function getPreviousBusinessDay(): \DateTimeImmutable
    {
        $previousBusinessDay = new \DateTimeImmutable();
        $previousBusinessDay = $previousBusinessDay->modify('- 1 day');
        while (
            (int)$previousBusinessDay->format('N') === 6 ||
            (int)$previousBusinessDay->format('N') === 7
        ) {
            $previousBusinessDay = $previousBusinessDay->modify('- 1 day');
        }

        return $previousBusinessDay;
    }
}
