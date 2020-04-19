<?php

namespace App\Tests\Unit;

use App\Config\Config;
use App\Entity\User;
use App\HttpClient\Issue as HttpIssue;
use App\HttpClient\TimeEntry as HttpTimeEntry;
use App\Repository\UserRepository;
use App\Service\Manager;
use App\Tests\Mock\RedmineHttpClientMock;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ManagerTest extends WebTestCase
{
    /** @var UserRepository */
    private $userRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RedmineHttpClientMock */
    private $httpClient;

    /** @var Manager */
    private $manager;

    public function __construct()
    {
        self::bootKernel();

        $this->userRepository = self::$container->get(UserRepository::class);
        $this->entityManager  = self::$container->get(EntityManagerInterface::class);

        /** @var LoggerInterface $logger */
        $logger = self::$container->get(LoggerInterface::class);

        $this->httpClient = new RedmineHttpClientMock();

        $this->manager = new Manager($this->entityManager, $this->httpClient, $logger);

        $config = new Config();
        $config->setMaxDailyHours(9);
        $config->setStatusNewId(RedmineHttpClientMock::statusNewId);
        $config->setStatusInProgressId(RedmineHttpClientMock::statusInProgressId);

        $this->manager->setConfig($config);

        $this->userRepository->deleteAll();

        $user = new User(RedmineHttpClientMock::userId);
        $user->setActive(true);
        $user->setLogin(RedmineHttpClientMock::userLogin);
        $user->setTrackTime(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        parent::__construct();
    }

    public function testSets()
    {
        $tests = [
            // multiple
            [
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        ),
                        new HttpIssue(
                            2,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:10')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ]
                ),
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        ),
                        new HttpIssue(
                            2,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:10')),
                            RedmineHttpClientMock::statusNewId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:00'))
                )
            ],
            [
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        ),
                        new HttpIssue(
                            2,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:10')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:00')),
                ),
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        ),
                        new HttpIssue(
                            2,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:10')),
                            RedmineHttpClientMock::statusNewId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:00'))
                )
            ],
            // one
            [
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:00')),
                ),
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:00'))
                )
            ],
            [
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ]
                ),
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:00'))
                ),
                1,
                new \DateTimeImmutable(date('Y-m-d H:i:30'))
            ],
            [
                new TestSet(
                    [
                        new HttpIssue(
                            2,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:30'))
                ),
                new TestSet(
                    [
                        new HttpIssue(
                            2,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('Y-m-d H:i:30')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ],
                    2,
                    new \DateTimeImmutable(date('Y-m-d H:i:30')),
                    new HttpTimeEntry(RedmineHttpClientMock::userId, 1, 0.0)
                ),
            ],
            //no one
            [
                new TestSet(),
                new TestSet(),
            ],
            [
                new TestSet(
                    [],
                    1,
                    new \DateTimeImmutable(date('Y-m-d H:i:30'))
                ),
                new TestSet(
                    [],
                    null,
                    null,
                    new HttpTimeEntry(RedmineHttpClientMock::userId, 1, 0.0)
                ),
            ],
            //max hours
            [
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('1970-01-01 00:00:00')),
                            RedmineHttpClientMock::statusInProgressId
                        )
                    ],
                    1,
                    new \DateTimeImmutable(date('1970-01-01 00:00:00'))
                ),
                new TestSet(
                    [
                        new HttpIssue(
                            1,
                            RedmineHttpClientMock::userId,
                            new \DateTimeImmutable(date('1970-01-01 00:00:00')),
                            RedmineHttpClientMock::statusNewId
                        )
                    ],
                    null,
                    null,
                    new HttpTimeEntry(RedmineHttpClientMock::userId, 1, 0.0)
                ),
            ],
        ];

        foreach ($tests as $test) {
            $this->check($test[0], $test[1]);
        }
    }

    private function check(TestSet $input, TestSet $output): void
    {
        $this->httpClient->init();

        $this->httpClient->issues = $input->issues;

        $user = $this->getUser();

        $user->setCurrentTaskId($input->userCurrentTaskId);
        $user->setCurrentTaskStartedAt($input->userCurrentTaskStartedAt);

        $this->entityManager->flush();

        $this->manager->manage([$user]);

        $user = $this->getUser();

        $this->assertEquals($output->userCurrentTaskId, $user->getCurrentTaskId());

        foreach ($this->httpClient->issues as $issue) {
            $outputIssue = $output->getIssueById($issue->getId());

            $this->assertNotNull($outputIssue);

            $this->assertEquals($outputIssue->getStatusId(), $issue->getStatusId());
        }

        if ($output->timeEntry !== null) {
            $this->assertEquals(1, count($this->httpClient->timeEntries));
            $this->assertEquals($output->timeEntry->getIssueId(), $this->httpClient->timeEntries[0]->getIssueId());
        } else {
            $this->assertEquals(0, count($this->httpClient->timeEntries));
        }
    }

    private function getUser(): User
    {
        $this->entityManager->clear();

        $user = $this->userRepository->findOneById(RedmineHttpClientMock::userId);

        $this->assertNotNull($user);

        return $user;
    }
}