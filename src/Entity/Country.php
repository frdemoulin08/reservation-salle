<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'country')]
#[UniqueEntity(fields: ['code'], message: 'country.code.unique', errorPath: 'code')]
class Country
{
    use TimestampableEntity;

    #[ORM\Column(length: 36, unique: true)]
    private string $publicIdentifier = '';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 2, unique: true)]
    private string $code = '';

    #[ORM\Column(length: 100)]
    private string $label = '';

    #[ORM\Column(length: 8, nullable: true)]
    private ?string $dialingCode = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    public function __construct()
    {
        $this->publicIdentifier = Uuid::v4()->toRfc4122();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicIdentifier(): string
    {
        return $this->publicIdentifier;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = strtoupper($code);

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getDialingCode(): ?string
    {
        return $this->dialingCode;
    }

    public function setDialingCode(?string $dialingCode): self
    {
        $this->dialingCode = $dialingCode;

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
}
