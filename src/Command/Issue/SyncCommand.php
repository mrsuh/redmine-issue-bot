<?php

namespace App\Command\Issue;

use App\Repository\UserRepository;
use App\Service\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    protected static $defaultName = 'issue:sync';

    private $manager;
    private $userRepository;

    protected function configure()
    {
        $this->addOption('period', null, InputOption::VALUE_OPTIONAL, 'Option description', 3);
    }

    public function __construct(Manager $manager, UserRepository $userRepository)
    {
        $this->manager        = $manager;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $period = (int)$input->getOption('period');

        while (true) {
            $this->manager->manage($this->userRepository->findAll());
            sleep($period);
        }

        return 0;
    }
}
