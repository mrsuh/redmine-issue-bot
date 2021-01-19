<?php

namespace App\Command\User;

use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected static $defaultName = 'user:list';

    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders([
            'redmine id',
            'redmine login',
            'telegram login'
        ]);

        foreach ($this->userRepository->findAll() as $user) {
            $table->addRow([
                $user->getRedmineId(),
                $user->getRedmineLogin(),
                $user->getTelegramLogin()
            ]);
        }

        $table->render();

        return 0;
    }
}
