<?php

namespace App\Habit\Application\Command;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:habits:mark-skipped',
    description: 'Пометить просроченные pending-логи привычек как skipped',
)]
final class MarkSkippedHabitLogsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new DateTimeImmutable('today');
        $conn = $this->em->getConnection();

        // Все pending-логи за прошедшие дни считаем пропущенными.
        // logged_at < today, status = 'pending' -> status = 'skipped'
        $affected = $conn->executeStatement(
            'UPDATE habit_logs SET status = :skipped WHERE status = :pending AND logged_at < :today',
            [
                'skipped' => 'skipped',
                'pending' => 'pending',
                'today' => $today->format('Y-m-d'),
            ]
        );

        $output->writeln(sprintf('Marked %d habit logs as skipped.', $affected));

        return Command::SUCCESS;
    }
}

