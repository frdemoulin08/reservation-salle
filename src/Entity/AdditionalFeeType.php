<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class AdditionalFeeType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $code = '';

    #[ORM\Column(length: 255)]
    private string $label = '';

    /**
     * @var Collection<int, ReservationAdditionalFee>
     */
    #[ORM\OneToMany(mappedBy: 'additionalFeeType', targetEntity: ReservationAdditionalFee::class)]
    private Collection $reservationAdditionalFees;

    public function __construct()
    {
        $this->reservationAdditionalFees = new ArrayCollection();
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
        $this->code = $code;

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

    /**
     * @return Collection<int, ReservationAdditionalFee>
     */
    public function getReservationAdditionalFees(): Collection
    {
        return $this->reservationAdditionalFees;
    }
}
