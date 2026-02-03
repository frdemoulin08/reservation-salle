<?php

namespace App\Entity;

use App\Repository\SiteDocumentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SiteDocumentTypeRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'site_document_type.code.unique', errorPath: 'code')]
class SiteDocumentType
{
    use TimestampableEntity;

    public const CODE_PHOTO = 'PHOTO';
    public const CODE_PLAN = 'PLAN';
    public const CODE_OTHER = 'OTHER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $code = '';

    #[ORM\Column(length: 255)]
    private string $label = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isPublic = true;

    #[ORM\Column(options: ['default' => false])]
    private bool $isRequired = false;

    #[ORM\Column(options: ['default' => true])]
    private bool $isMultipleAllowed = true;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    /**
     * @var Collection<int, VenueDocument>
     */
    #[ORM\OneToMany(mappedBy: 'documentType', targetEntity: VenueDocument::class)]
    private Collection $documents;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = mb_strtoupper(trim($code));

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function isMultipleAllowed(): bool
    {
        return $this->isMultipleAllowed;
    }

    public function setIsMultipleAllowed(bool $isMultipleAllowed): self
    {
        $this->isMultipleAllowed = $isMultipleAllowed;

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

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function __toString(): string
    {
        return $this->label;
    }

    /**
     * @return Collection<int, VenueDocument>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }
}
