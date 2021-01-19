<?php

namespace App\Command\Status;

use App\Repository\StatusRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    protected static $defaultName = 'status:list';

    private $statusRepository;

    public function __construct(StatusRepository $statusRepository)
    {
        $this->statusRepository = $statusRepository;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $table = new Table($output);
        $table->setHeaders([
            'redmine id',
            'redmine name',
            'for review',
            'type'
        ]);

        foreach ($this->statusRepository->findAll() as $user) {
            $table->addRow([
                $user->getRedmineId(),
                $user->getRedmineName(),
                $user->getForReview() ? '+' : '',
                $user->getType(),
            ]);
        }

        $table->render();

        return 0;
    }
}
