<?php

declare(strict_types=1);

namespace App\Reminder\Application\Service;

use App\Reminder\Domain\Repository\ReminderRepositoryInterface;
use App\Reminder\Infrastructure\Persistence\ReminderEntity;
use DateMalformedStringException;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Отправка e-mail по просроченным (due) активным напоминаниям и сдвиг notify_at / статуса по frequency.
 */
final class ReminderDueEmailSender
{
    public function __construct(
        private readonly ReminderRepositoryInterface $reminderRepository,
        private readonly MailerInterface $mailer,
        private readonly string $mailerFrom,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Обрабатывает все напоминания с notify_at <= $now.
     *
     * @return int количество успешно отправленных писем
     */
    public function sendDue(DateTimeImmutable $now): int
    {
        $due = $this->reminderRepository->findAllDueActive($now);
        $sent = 0;

        foreach ($due as $reminder) {
            try {
                $this->sendOne($reminder);
                $this->advanceAfterSend($reminder);
                $this->reminderRepository->save($reminder, true);
                ++$sent;
            } catch (\Throwable $e) {
                $this->logger->error('Reminder email failed', [
                    'reminder_id' => $reminder->getId(),
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $sent;
    }

    private function sendOne(ReminderEntity $reminder): void
    {
        $user = $reminder->getUser();
        $body = $this->buildTextBody($reminder);

        $message = (new Email())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject($reminder->getTitle())
            ->text($body);

        $this->mailer->send($message);
    }

    private function buildTextBody(ReminderEntity $reminder): string
    {
        $lines = [];
        if ($reminder->getDescription() !== null && $reminder->getDescription() !== '') {
            $lines[] = $reminder->getDescription();
            $lines[] = '';
        }
        $lines[] = 'Время напоминания: '.$reminder->getNotifyAt()->format('Y-m-d H:i');
        if ($reminder->getEntityType() !== null) {
            $lines[] = sprintf('Связь: %s #%s', $reminder->getEntityType(), (string) ($reminder->getEntityId() ?? ''));
        }
        $lines[] = '';
        $lines[] = '— Novera';

        return implode("\n", $lines);
    }

    private function advanceAfterSend(ReminderEntity $reminder): void
    {
        $frequency = $reminder->getFrequency();
        $current = $reminder->getNotifyAt();

        if ($frequency === 'none') {
            $reminder->setStatus('done');

            return;
        }

        if ($frequency === 'daily') {
            $reminder->setNotifyAt($current->modify('+1 day'));

            return;
        }

        if ($frequency === 'weekly' || $frequency === 'custom') {
            $days = $reminder->getWeekDays();
            if ($days === null || $days === []) {
                if ($frequency === 'weekly') {
                    $reminder->setNotifyAt($current->modify('+7 days'));
                } else {
                    $reminder->setStatus('done');
                }

                return;
            }

            $reminder->setNotifyAt($this->nextWeekdayOccurrence($current, $days));

            return;
        }

        $reminder->setStatus('done');
    }

    /**
     * @param list<int> $weekDays
     * @throws DateMalformedStringException
     */
    private function nextWeekdayOccurrence(DateTimeImmutable $from, array $weekDays): DateTimeImmutable
    {
        $weekDays = array_values(array_unique(array_map('intval', $weekDays)));
        sort($weekDays);

        $hour = (int) $from->format('H');
        $minute = (int) $from->format('i');
        $second = (int) $from->format('s');

        for ($add = 1; $add <= 366; ++$add) {
            $candidate = $from->modify("+{$add} days")->setTime($hour, $minute, $second);
            $w = (int) $candidate->format('w');
            if (\in_array($w, $weekDays, true)) {
                return $candidate;
            }
        }

        return $from->modify('+7 days');
    }
}
