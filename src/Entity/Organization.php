<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true, unique: true)]
    private ?string $siret = null;

    #[ORM\Column(length: 255)]
    private string $legalName = '';

    #[ORM\Column(length: 255)]
    private string $displayName = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $legalNature = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $organizationType = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $billingSameAsHeadOffice = false;

    #[ORM\Embedded(class: Address::class, columnPrefix: 'head_office_')]
    private ?Address $headOfficeAddress = null;

    #[ORM\Embedded(class: Address::class, columnPrefix: 'billing_')]
    private ?Address $billingAddress = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: User::class)]
    private Collection $users;

    /**
     * @var Collection<int, OrganizationContact>
     */
    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: OrganizationContact::class)]
    private Collection $contacts;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(mappedBy: 'organization', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->headOfficeAddress = new Address();
        $this->billingAddress = new Address();
        $this->users = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(?string $siret): self
    {
        $this->siret = $siret;

        return $this;
    }

    public function getLegalName(): string
    {
        return $this->legalName;
    }

    public function setLegalName(string $legalName): self
    {
        $this->legalName = $legalName;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getLegalNature(): ?string
    {
        return $this->legalNature;
    }

    public function setLegalNature(?string $legalNature): self
    {
        $this->legalNature = $legalNature;

        return $this;
    }

    public function getOrganizationType(): ?string
    {
        return $this->organizationType;
    }

    public function setOrganizationType(?string $organizationType): self
    {
        $this->organizationType = $organizationType;

        return $this;
    }

    public function isBillingSameAsHeadOffice(): bool
    {
        return $this->billingSameAsHeadOffice;
    }

    public function setBillingSameAsHeadOffice(bool $billingSameAsHeadOffice): self
    {
        $this->billingSameAsHeadOffice = $billingSameAsHeadOffice;

        return $this;
    }

    public function getHeadOfficeAddress(): ?Address
    {
        return $this->headOfficeAddress;
    }

    public function setHeadOfficeAddress(?Address $headOfficeAddress): self
    {
        $this->headOfficeAddress = $headOfficeAddress;

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setOrganization($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            if ($user->getOrganization() === $this) {
                $user->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrganizationContact>
     */
    public function getContacts(): Collection
    {
        return $this->contacts;
    }

    public function addContact(OrganizationContact $contact): self
    {
        if (!$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setOrganization($this);
        }

        return $this;
    }

    public function removeContact(OrganizationContact $contact): self
    {
        if ($this->contacts->removeElement($contact)) {
            if ($contact->getOrganization() === $this) {
                $contact->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): self
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setOrganization($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getOrganization() === $this) {
                $reservation->setOrganization(null);
            }
        }

        return $this;
    }
}
