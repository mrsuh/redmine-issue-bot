<?php

namespace App\Tests\Unit;

use App\Config\Loader;
use App\Repository\UserRepository;
use App\Tests\Mock\RedmineHttpClientMock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConfigTest extends WebTestCase
{
    public function testLoad()
    {
        self::bootKernel();

        /** @var UserRepository $userRepository */
        $userRepository = self::$container->get(UserRepository::class);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        $httpClient = new RedmineHttpClientMock();

        $loader = new Loader($httpClient, $userRepository, $entityManager);

        $config = $loader->load();

        $this->assertEquals($config->getMaxDailyHours(), 9);

        $this->assertEquals($config->getStatusNewId(), 1);
        $this->assertEquals($config->getStatusInProgressId(), 2);

        $user = $userRepository->findOneById(5);

        $this->assertNotNull($user);

        $this->assertEquals($user->getLogin(), 'userLogin');
    }
}