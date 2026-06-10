<?php

namespace App\Telegram\Application\Service;

use App\Auth\Infrastructure\Repository\RefreshTokenRepository;
use App\Telegram\Application\DTO\TelegramAuthResponseDto;
use App\Telegram\Application\DTO\TelegramConnectLinkResponseDto;
use App\Telegram\Domain\Repository\TelegramLinkTokenRepositoryInterface;
use App\Telegram\Infrastructure\Persistence\TelegramLinkTokenEntity;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Persistence\UserEntity;
use DateMalformedStringException;
use DateTimeImmutable;
use JsonException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Random\RandomException;
use RuntimeException;

final readonly class TelegramService
{
    private string $telegramBotToken;
    private string $telegramBotUsername;
    private string $telegramMiniAppUrl;

    public function __construct(
        private TelegramLinkTokenRepositoryInterface $telegramLinkTokenRepository,
        private UserRepositoryInterface $userRepository,
        private JWTTokenManagerInterface $jwtTokenManager,
        private RefreshTokenRepository $refreshTokenRepository,
        ?string $telegramBotToken,
        ?string $telegramBotUsername,
        ?string $telegramMiniAppUrl,
    ) {
        $this->telegramBotToken = $telegramBotToken ?? '';
        $this->telegramBotUsername = $telegramBotUsername ?? '';
        $this->telegramMiniAppUrl = $telegramMiniAppUrl ?? '';
    }

    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function generateConnectLink(UserEntity $user): TelegramConnectLinkResponseDto
    {
        $this->assertBotConfigured();
        $this->telegramLinkTokenRepository->removeActiveTokensForUser($user);

        $token = bin2hex(random_bytes(32));
        $expiresAt = new DateTimeImmutable('+30 minutes');

        $entity = new TelegramLinkTokenEntity($user, $token, $expiresAt);
        $this->telegramLinkTokenRepository->save($entity);

        return new TelegramConnectLinkResponseDto(
            url: sprintf('https://t.me/%s?start=%s', $this->telegramBotUsername, $token),
            expires_at: $expiresAt->format(DATE_ATOM),
        );
    }

    public function handleWebhook(array $payload): void
    {
        $message = $payload['message'] ?? $payload['edited_message'] ?? null;
        if (!is_array($message)) {
            return;
        }

        $text = trim((string) ($message['text'] ?? ''));
        if ($text === '' || !str_starts_with($text, '/start')) {
            return;
        }

        $parts = preg_split('/\s+/', $text);
        $token = $parts[1] ?? '';
        if ($token === '') {
            return;
        }

        $telegramUserId = (string) ($message['from']['id'] ?? '');
        $chatId = (string) ($message['chat']['id'] ?? $telegramUserId);
        if ($telegramUserId === '') {
            return;
        }

        $linkToken = $this->telegramLinkTokenRepository->findByToken($token);
        if (!$linkToken instanceof TelegramLinkTokenEntity || $linkToken->isExpired() || $linkToken->isConsumed()) {
            $this->sendMessage($chatId, 'Ссылка привязки недействительна или уже использована. Сгенерируйте новую в Novera.');
            return;
        }

        $user = $linkToken->getUser();
        $existingUser = $this->userRepository->findByTelegramId($telegramUserId);
        if ($existingUser instanceof UserEntity && $existingUser->getId() !== $user->getId()) {
            $this->sendMessage($chatId, 'Этот Telegram уже привязан к другому аккаунту Novera.');
            return;
        }

        $user->setTelegramId($telegramUserId);
        $this->userRepository->save($user);

        $linkToken->consume();
        $this->telegramLinkTokenRepository->save($linkToken);

        $this->sendLaunchMessage($chatId, $user->getFullName());
    }

    /**
     * @throws JsonException
     */
    public function authenticateByInitData(string $initData): TelegramAuthResponseDto
    {
        $telegramUser = $this->validateInitData($initData);
        $telegramUserId = (string) ($telegramUser['id'] ?? '');

        if ($telegramUserId === '') {
            throw new RuntimeException('Telegram user id not found');
        }

        $user = $this->userRepository->findByTelegramId($telegramUserId);
        if (!$user instanceof UserEntity) {
            throw new RuntimeException('Telegram account is not linked to a Novera user');
        }

        $this->refreshTokenRepository->removeAllForUser($user);
        $refreshToken = $this->refreshTokenRepository->createForUser($user, '+14 days');

        return new TelegramAuthResponseDto(
            token: $this->jwtTokenManager->create($user),
            refresh_token: $refreshToken->getToken(),
        );
    }

    public function getBotUsername(): ?string
    {
        return $this->telegramBotUsername !== '' ? $this->telegramBotUsername : null;
    }

    public function getMiniAppUrl(): ?string
    {
        return $this->telegramMiniAppUrl !== '' ? $this->telegramMiniAppUrl : null;
    }

    private function assertBotConfigured(): void
    {
        if ($this->telegramBotToken === '' || $this->telegramBotUsername === '') {
            throw new RuntimeException('Telegram bot is not configured');
        }
    }

    /**
     * @return array<string, mixed>
     * @throws JsonException
     */
    private function validateInitData(string $initData): array
    {
        $this->assertBotConfigured();

        parse_str($initData, $data);
        $hash = (string) ($data['hash'] ?? '');
        if ($hash === '') {
            throw new RuntimeException('Telegram init data hash is missing');
        }

        unset($data['hash']);
        ksort($data);

        $checkStringLines = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                continue;
            }

            $checkStringLines[] = sprintf('%s=%s', $key, (string) $value);
        }

        $dataCheckString = implode("\n", $checkStringLines);
        $secretKey = hash_hmac('sha256', $this->telegramBotToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        if (!hash_equals($calculatedHash, $hash)) {
            throw new RuntimeException('Telegram init data validation failed');
        }

        $authDate = (int) ($data['auth_date'] ?? 0);
        if ($authDate <= 0 || $authDate < time() - 86400) {
            throw new RuntimeException('Telegram init data is expired');
        }

        $userJson = (string) ($data['user'] ?? '');
        if ($userJson === '') {
            throw new RuntimeException('Telegram user payload is missing');
        }

        $user = json_decode($userJson, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($user)) {
            throw new RuntimeException('Telegram user payload is invalid');
        }

        return $user;
    }

    private function sendLaunchMessage(string $chatId, string $fullName): void
    {
        $this->assertBotConfigured();

        $payload = [
            'chat_id' => $chatId,
            'text' => sprintf('Telegram подключен к Novera для %s.', $fullName),
        ];

        if ($this->telegramMiniAppUrl !== '') {
            $payload['reply_markup'] = [
                'inline_keyboard' => [
                    [
                        [
                            'text' => 'Открыть Novera',
                            'web_app' => ['url' => $this->telegramMiniAppUrl],
                        ],
                    ],
                ],
            ];
        }

        $this->sendTelegramRequest('sendMessage', $payload);
    }

    private function sendMessage(string $chatId, string $text): void
    {
        $this->assertBotConfigured();
        $this->sendTelegramRequest('sendMessage', [
            'chat_id' => $chatId,
            'text' => $text,
        ]);
    }

    private function sendTelegramRequest(string $method, array $payload): void
    {
        $url = sprintf('https://api.telegram.org/bot%s/%s', $this->telegramBotToken, $method);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ignore_errors' => true,
                'timeout' => 10,
            ],
        ]);

        @file_get_contents($url, false, $context);
    }
}
