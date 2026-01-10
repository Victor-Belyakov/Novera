<?php

namespace App\Auth\Infrastructure\Persistence;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use App\User\Infrastructure\Persistence\UserEntity;

#[ORM\Entity]
#[ORM\Table(name: 'refresh_tokens')]
#[ORM\UniqueConstraint(name: 'uniq_refresh_token', columns: ['token'])]
class RefreshTokenEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private string $token;

    #[ORM\Column]
    private DateTimeImmutable $expiresAt;

    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private UserEntity $user;

    /**
     * @param UserEntity $user
     * @param string $token
     * @param DateTimeImmutable $expiresAt
     */
    public function __construct(
        UserEntity $user,
        string $token,
        DateTimeImmutable $expiresAt
    ) {
        $this->user = $user;
        $this->token = $token;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->user;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTimeImmutable();
    }
}
