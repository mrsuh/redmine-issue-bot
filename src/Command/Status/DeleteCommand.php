<?php

namespace App\Command\Status;

use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteCommand extends Command
{
    protected static $defaultName = 'status:delete';

    private EntityManagerInterface $entityManager;
    private StatusRepository       $statusRepository;

    protected function configure(): void
    {
        $this->addOption('redmineId', null, InputOption::VALUE_REQUIRED, 'Option description', 0);
    }

    public function __construct(StatusRepository $statusRepository, EntityManagerInterface $entityManager)
    {
        $this->statusRepository = $statusRepository;
        $this->entityManager    = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $redmineId = (int)$input->getOption('redmineId');

        $status = $this->statusRepository->findOneByRedmineId($redmineId);
        if ($status === null) {
            $output->writeln('<error>Status with id not found</error>');
        }

        $this->entityManager->remove($status);
        $this->entityManager->flush();

        $output->writeln('<info>Status successfully deleted</info>');

        return 0;
    }
}
