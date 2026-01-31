<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class RoomService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'roomServices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\ManyToOne(inversedBy: 'roomServices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceType $serviceType = null;

    #[ORM\Column(options: ['default' => true])]
    private bool $isIncluded = true;

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

    public function getServiceType(): ?ServiceType
    {
        return $this->serviceType;
    }

    public function setServiceType(?ServiceType $serviceType): self
    {
        $this->serviceType = $serviceType;

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
