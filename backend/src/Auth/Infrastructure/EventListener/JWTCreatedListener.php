<?php

namespace App\Auth\Infrastructure\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use App\User\Infrastructure\Persistence\UserEntity;

final class JWTCreatedListener
{
    /**
     * @param JWTCreatedEvent $event
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof UserEntity) {
            return;
        }

        $payload = [
            'id' => $user->getId(),
            'username' => $user->getEmail(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'iat' => time(),
            'exp' => time() + 900,
        ];

        $event->setData($payload);
    }
}
