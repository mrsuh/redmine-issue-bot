<?php

namespace App\Command\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{
    protected static $defaultName = 'user:delete';

    private EntityManagerInterface $entityManager;
    private UserRepository         $userRepository;

    protected function configure(): void
    {
        $this->addOption('redmineId', null, InputOption::VALUE_REQUIRED, 'Option description', 0);
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
            $output->writeln('<error>User with id not found</error>');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $output->writeln('<info>User successfully deleted</info>');

        return 0;
    }
}
