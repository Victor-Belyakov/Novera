<?php

declare(strict_types=1);

namespace App\Reminder\Application\Command;

use App\Reminder\Application\Service\ReminderDueEmailSender;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:reminders:send-due-emails',
    description: 'Отправить e-mail по всем активным напоминаниям, у которых наступило время (notify_at <= now)',
)]
final class SendDueReminderEmailsCommand extends Command
{
    public function __construct(
        private readonly ReminderDueEmailSender $reminderDueEmailSender,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new DateTimeImmutable();
        $sent = $this->reminderDueEmailSender->sendDue($now);
        $output->writeln(sprintf('Sent %d reminder email(s).', $sent));

        return Command::SUCCESS;
    }
}
