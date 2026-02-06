<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class EquipmentType
{
    use TimestampableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private string $code = '';

    #[ORM\Column(length: 255)]
    private string $label = '';

    /**
     * @var Collection<int, RoomEquipment>
     */
    #[ORM\OneToMany(mappedBy: 'equipmentType', targetEntity: RoomEquipment::class)]
    private Collection $roomEquipments;

    /**
     * @var Collection<int, VenueEquipment>
     */
    #[ORM\OneToMany(mappedBy: 'equipmentType', targetEntity: VenueEquipment::class)]
    private Collection $venueEquipments;

    /**
     * @var Collection<int, Equipment>
     */
    #[ORM\OneToMany(mappedBy: 'equipmentType', targetEntity: Equipment::class)]
    private Collection $equipments;

    public function __construct()
    {
        $this->roomEquipments = new ArrayCollection();
        $this->venueEquipments = new ArrayCollection();
        $this->equipments = new ArrayCollection();
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
     * @return Collection<int, RoomEquipment>
     */
    public function getRoomEquipments(): Collection
    {
        return $this->roomEquipments;
    }

    /**
     * @return Collection<int, VenueEquipment>
     */
    public function getVenueEquipments(): Collection
    {
        return $this->venueEquipments;
    }

    /**
     * @return Collection<int, Equipment>
     */
    public function getEquipments(): Collection
    {
        return $this->equipments;
    }
}
