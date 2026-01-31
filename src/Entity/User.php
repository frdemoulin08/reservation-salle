<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'app_user')]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['publicIdentifier'], message: 'There is already an account with this public identifier')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    public const ROLE_BUSINESS_ADMIN = 'ROLE_BUSINESS_ADMIN';
    public const ROLE_APP_MANAGER = 'ROLE_APP_MANAGER';
    public const ROLE_SUPERVISOR = 'ROLE_SUPERVISOR';
    public const ROLE_USER = 'ROLE_USER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $password = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 26, unique: true)]
    private ?string $publicIdentifier = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $lastname = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $mobilePhone = null;

    #[ORM\Column(type: Types::STRING, length: 20, nullable: true)]
    private ?string $fixedPhone = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    /**
     * @var Collection<int, AuthenticationLog>
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: AuthenticationLog::class, cascade: ['remove'])]
    #[ORM\OrderBy(['occurredAt' => 'DESC'])]
    private Collection $authenticationLogs;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\ManyToMany(targetEntity: Role::class)]
    #[ORM\JoinTable(name: 'user_role')]
    private Collection $roles;

    public function __construct()
    {
        $this->authenticationLogs = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->publicIdentifier = (string) new Ulid();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = [];

        foreach ($this->roles as $role) {
            if ($role->isActive()) {
                $roles[] = $role->getCode();
            }
        }

        $roles[] = self::ROLE_USER;

        return array_values(array_unique($roles));
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN);
    }

    public function isBusinessAdmin(): bool
    {
        return $this->hasRole(self::ROLE_BUSINESS_ADMIN);
    }

    public function isAppManager(): bool
    {
        return $this->hasRole(self::ROLE_APP_MANAGER);
    }

    public function isSupervisor(): bool
    {
        return $this->hasRole(self::ROLE_SUPERVISOR);
    }

    /**
     * @return Collection<int, Role>
     */
    public function getRoleEntities(): Collection
    {
        return $this->roles;
    }

    public function addRoleEntity(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
            if (!$role->getUsers()->contains($this)) {
                $role->addUser($this);
            }
        }

        return $this;
    }

    public function removeRoleEntity(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            if ($role->getUsers()->contains($this)) {
                $role->removeUser($this);
            }
        }

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // clear temporary sensitive data here if needed
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = mb_strtolower($email);

        return $this;
    }

    public function getPublicIdentifier(): ?string
    {
        return $this->publicIdentifier;
    }

    public function setPublicIdentifier(?string $publicIdentifier): self
    {
        $this->publicIdentifier = $publicIdentifier;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getMobilePhone(): ?string
    {
        return $this->mobilePhone;
    }

    public function setMobilePhone(?string $mobilePhone): self
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }

    public function getFixedPhone(): ?string
    {
        return $this->fixedPhone;
    }

    public function setFixedPhone(?string $fixedPhone): self
    {
        $this->fixedPhone = $fixedPhone;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, AuthenticationLog>
     */
    public function getAuthenticationLogs(): Collection
    {
        return $this->authenticationLogs;
    }

    public function addAuthenticationLog(AuthenticationLog $authenticationLog): self
    {
        if (!$this->authenticationLogs->contains($authenticationLog)) {
            $this->authenticationLogs->add($authenticationLog);
            $authenticationLog->setUser($this);
        }

        return $this;
    }

    public function removeAuthenticationLog(AuthenticationLog $authenticationLog): self
    {
        if ($this->authenticationLogs->removeElement($authenticationLog)) {
            if ($authenticationLog->getUser() === $this) {
                $authenticationLog->setUser(null);
            }
        }

        return $this;
    }
}
