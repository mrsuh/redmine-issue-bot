<?php

namespace App\Command\Status;

use App\Entity\Status;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command
{
    protected static $defaultName = 'status:upsert';

    private EntityManagerInterface $entityManager;
    private StatusRepository       $statusRepository;

    protected function configure(): void
    {
        $this->addOption('redmineId', null, InputOption::VALUE_REQUIRED, 'Option description', 0);
        $this->addOption('redmineName', null, InputOption::VALUE_REQUIRED, 'Option description', '');
        $this->addOption('isForReview', null, InputOption::VALUE_OPTIONAL, 'Option description', false);
        $this->addOption('type', null, InputOption::VALUE_OPTIONAL, implode(',', [Status::NEW, Status::IN_PROGRESS, Status::CLOSED]), '');
    }

    public function __construct(StatusRepository $statusRepository, EntityManagerInterface $entityManager)
    {
        $this->statusRepository = $statusRepository;
        $this->entityManager    = $entityManager;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $input->getOption('type');
        if (!empty($type) && !in_array($type, [Status::NEW, Status::IN_PROGRESS, Status::CLOSED])) {
            $output->writeln('<error>Invalid type option</error>');

            return 1;
        }

        $redmineId = (int)$input->getOption('redmineId');

        $status = $this->statusRepository->findOneByRedmineId($redmineId);
        if ($status === null) {
            $status = new Status();
            $this->entityManager->persist($status);
        }

        $status->setRedmineId($redmineId);
        $status->setRedmineName($input->getOption('redmineName'));
        $status->setType($type);
        $status->setForReview((bool)$input->getOption('isForReview'));

        $this->entityManager->flush();

        $output->writeln('<info>Status successfully added</info>');

        return 0;
    }
}
