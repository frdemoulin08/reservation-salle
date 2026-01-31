<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ReservationAdditionalFee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'additionalFees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\ManyToOne(inversedBy: 'reservationAdditionalFees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AdditionalFeeType $additionalFeeType = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $amount = '0.00';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $label = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): self
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getAdditionalFeeType(): ?AdditionalFeeType
    {
        return $this->additionalFeeType;
    }

    public function setAdditionalFeeType(?AdditionalFeeType $additionalFeeType): self
    {
        $this->additionalFeeType = $additionalFeeType;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
