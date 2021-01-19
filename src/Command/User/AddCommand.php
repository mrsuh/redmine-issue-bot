<?php

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{
    protected static $defaultName = 'user:upsert';

    private EntityManagerInterface $entityManager;
    private UserRepository         $userRepository;

    protected function configure(): void
    {
        $this->addOption('redmineId', null, InputOption::VALUE_REQUIRED, 'Option description', 0);
        $this->addOption('redmineLogin', null, InputOption::VALUE_REQUIRED, 'Option description', '');
        $this->addOption('telegramLogin', null, InputOption::VALUE_OPTIONAL, 'Option description', '');
    }

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager  = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $redmineId = (int)$input->getOption('redmineId');

        $user = $this->userRepository->findOneByRedmineId($redmineId);
        if ($user === null) {
            $user = new User();
            $this->entityManager->persist($user);
        }

        $user->setRedmineId($redmineId);
        $user->setRedmineLogin($input->getOption('redmineLogin'));
        $user->setTelegramLogin($input->getOption('telegramLogin'));

        $this->entityManager->flush();

        $output->writeln('<info>User successfully added</info>');

        return 0;
    }
}
