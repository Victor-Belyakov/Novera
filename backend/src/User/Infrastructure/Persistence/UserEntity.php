<?php

namespace App\User\Infrastructure\Persistence;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
#[ORM\UniqueConstraint(name: 'uniq_user_email', columns: ['email'])]
class UserEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'json')]
    public array $roles = [];

    #[ORM\Column(type: 'string')]
    public string $password;

    #[ORM\Column(type: 'string')]
    private string $name;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $middle_name = null;

    #[ORM\Column(type: 'string')]
    private string $last_name;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $dateOfBirth;

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
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = mb_strtolower($email);
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getDateOfBirth(): DateTimeImmutable
    {
        return $this->dateOfBirth;
    }

    /**
     * @param DateTimeImmutable $dateOfBirth
     * @return $this
     */
    public function setDateOfBirth(DateTimeImmutable $dateOfBirth): UserEntity
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): UserEntity
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMiddleName(): ?string
    {
        return $this->middle_name;
    }

    /**
     * @param string|null $middle_name
     * @return $this
     */
    public function setMiddleName(?string $middle_name): UserEntity
    {
        $this->middle_name = $middle_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * @param string $last_name
     * @return $this
     */
    public function setLastName(string $last_name): UserEntity
    {
        $this->last_name = $last_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return sprintf(
            '%s %s %s',
            $user->last_name,
            $user->first_name,
            $user->middle_name
        );
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void {}
}
