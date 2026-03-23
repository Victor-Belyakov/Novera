<?php

namespace App\Habit\Application\Command;

use App\Habit\Infrastructure\Persistence\HabitEntity;
use App\Habit\Infrastructure\Persistence\HabitLogEntity;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:habits:generate-logs',
    description: 'Создать pending-логи для активных привычек на сегодня по их frequency',
)]
final class GenerateHabitLogsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new DateTimeImmutable('today');
        $dayOfWeek = (int) $today->format('w'); // 0 = воскресенье, 1 = понедельник, ...
        $dayOfMonth = (int) $today->format('j'); // 1-31
        $isMonday = $dayOfWeek === 1;
        $isTuesday = $dayOfWeek === 2;
        $isThursday = $dayOfWeek === 4;
        $isFirstOfMonth = $dayOfMonth === 1;

        // Получаем все активные привычки
        $habits = $this->em->createQueryBuilder()
            ->select('h')
            ->from(HabitEntity::class, 'h')
            ->where('h.status = :status')
            ->andWhere('h.deletedAt IS NULL')
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();

        $created = 0;
        $skipped = 0;
        $notApplicable = 0;

        foreach ($habits as $habit) {
            $shouldCreate = false;
            $frequency = $habit->getFrequency();

            // Проверяем, нужно ли создавать лог сегодня для этой привычки
            if ($frequency === 'daily') {
                $shouldCreate = true;
            } elseif ($frequency === 'weekly' && $isMonday) {
                $shouldCreate = true;
            } elseif ($frequency === '2_per_week' && ($isTuesday || $isThursday)) {
                $shouldCreate = true;
            } elseif ($frequency === '3_per_week') {
                // Понедельник (1), Среда (3), Пятница (5)
                if ($dayOfWeek === 1 || $dayOfWeek === 3 || $dayOfWeek === 5) {
                    $shouldCreate = true;
                }
            } elseif ($frequency === 'monthly' && $isFirstOfMonth) {
                $shouldCreate = true;
            }

            if (!$shouldCreate) {
                $notApplicable++;
                continue;
            }

            // Проверяем через SQL, есть ли уже лог за сегодня
            $conn = $this->em->getConnection();
            $existingLogCount = $conn->fetchOne(
                'SELECT COUNT(*) FROM habit_logs WHERE habit_id = :habit_id AND logged_at = :date',
                [
                    'habit_id' => $habit->getId(),
                    'date' => $today->format('Y-m-d'),
                ]
            );
            
            if ($existingLogCount > 0) {
                $skipped++;
                continue;
            }

            // Создаём новый pending-лог
            $log = new HabitLogEntity();
            $log->setLoggedAt($today);
            $log->setStatus('pending');
            $habit->addLogEntry($log);
            $this->em->persist($habit);
            $created++;
        }

        $this->em->flush();

        $output->writeln(sprintf('Created: %d new habit logs', $created));
        $output->writeln(sprintf('Skipped: %d (already exist)', $skipped));
        $output->writeln(sprintf('Not applicable: %d (frequency does not match today)', $notApplicable));

        return Command::SUCCESS;
    }
}
