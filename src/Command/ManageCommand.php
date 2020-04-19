<?php

namespace App\Command;

use App\Config\Loader;
use App\Repository\UserRepository;
use App\Service\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ManageCommand extends Command
{
    protected static $defaultName = 'manage';

    private $configLoader;
    private $manager;
    private $userRepository;

    protected function configure()
    {
        $this->addOption('period', null, InputOption::VALUE_OPTIONAL, 'Option description', 3);
    }

    public function __construct(Loader $configLoader, Manager $manager, UserRepository $userRepository)
    {
        $this->manager        = $manager;
        $this->configLoader   = $configLoader;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $period = (int)$input->getOption('period');

        $this->manager->setConfig($this->configLoader->load());

        $users = $this->userRepository->findActive();

        while (true) {
            $this->manager->manage($users);
            sleep($period);
        }

        return 0;
    }
}
