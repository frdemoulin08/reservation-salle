<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class VenueEquipment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'venueEquipments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Venue $venue = null;

    #[ORM\ManyToOne(inversedBy: 'venueEquipments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?EquipmentType $equipmentType = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxQuantity = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isIncluded = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVenue(): ?Venue
    {
        return $this->venue;
    }

    public function setVenue(?Venue $venue): self
    {
        $this->venue = $venue;

        return $this;
    }

    public function getEquipmentType(): ?EquipmentType
    {
        return $this->equipmentType;
    }

    public function setEquipmentType(?EquipmentType $equipmentType): self
    {
        $this->equipmentType = $equipmentType;

        return $this;
    }

    public function getMaxQuantity(): ?int
    {
        return $this->maxQuantity;
    }

    public function setMaxQuantity(?int $maxQuantity): self
    {
        $this->maxQuantity = $maxQuantity;

        return $this;
    }

    public function isIncluded(): bool
    {
        return $this->isIncluded;
    }

    public function setIsIncluded(bool $isIncluded): self
    {
        $this->isIncluded = $isIncluded;

        return $this;
    }
}
