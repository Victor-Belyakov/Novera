<?php

namespace App\Auth\Infrastructure\EventListener;

use App\Auth\Infrastructure\Persistence\RefreshTokenEntity;
use App\Auth\Infrastructure\Repository\RefreshTokenRepository;
use DateTimeImmutable;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

readonly class LoginSuccessListener
{
    /**
     * @param RefreshTokenRepository $refreshTokenRepository
     */
    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository
    ) {
    }

    /**
     * @param AuthenticationSuccessEvent $event
     * @return void
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $user = $event->getUser();

        $this->refreshTokenRepository->removeAllForUser($user);

        $refresh = new RefreshTokenEntity($user, bin2hex(random_bytes(32)), new DateTimeImmutable('+14 days'));

        $this->refreshTokenRepository->save($refresh);

        $data = $event->getData();
        $data['refresh_token'] = $refresh->getToken();
        $event->setData($data);
    }
}
