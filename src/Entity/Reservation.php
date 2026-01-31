<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class Reservation
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    #[ORM\ManyToOne]
    private ?OrganizationContact $organizationContact = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?EventType $eventType = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $endDate = null;

    #[ORM\Column(length: 50)]
    private string $status = 'DRAFT';

    #[ORM\Column(length: 50)]
    private string $ticketingType = 'NONE';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $securityDeposit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    /**
     * @var Collection<int, ReservationAdditionalFee>
     */
    #[ORM\OneToMany(mappedBy: 'reservation', targetEntity: ReservationAdditionalFee::class)]
    private Collection $additionalFees;

    public function __construct()
    {
        $this->additionalFees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getOrganizationContact(): ?OrganizationContact
    {
        return $this->organizationContact;
    }

    public function setOrganizationContact(?OrganizationContact $organizationContact): self
    {
        $this->organizationContact = $organizationContact;

        return $this;
    }

    public function getEventType(): ?EventType
    {
        return $this->eventType;
    }

    public function setEventType(?EventType $eventType): self
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTicketingType(): string
    {
        return $this->ticketingType;
    }

    public function setTicketingType(string $ticketingType): self
    {
        $this->ticketingType = $ticketingType;

        return $this;
    }

    public function getSecurityDeposit(): ?string
    {
        return $this->securityDeposit;
    }

    public function setSecurityDeposit(?string $securityDeposit): self
    {
        $this->securityDeposit = $securityDeposit;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection<int, ReservationAdditionalFee>
     */
    public function getAdditionalFees(): Collection
    {
        return $this->additionalFees;
    }

    public function addAdditionalFee(ReservationAdditionalFee $additionalFee): self
    {
        if (!$this->additionalFees->contains($additionalFee)) {
            $this->additionalFees->add($additionalFee);
            $additionalFee->setReservation($this);
        }

        return $this;
    }

    public function removeAdditionalFee(ReservationAdditionalFee $additionalFee): self
    {
        if ($this->additionalFees->removeElement($additionalFee)) {
            if ($additionalFee->getReservation() === $this) {
                $additionalFee->setReservation(null);
            }
        }

        return $this;
    }
}
