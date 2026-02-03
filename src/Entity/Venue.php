<?php

namespace App\Entity;

use App\Entity\Embeddable\Address;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: \App\Repository\VenueRepository::class)]
class Venue
{
    use TimestampableEntity;

    #[ORM\Column(length: 36, unique: true)]
    private string $publicIdentifier = '';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $publicTransportAccess = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $parkingType = null;

    #[ORM\Column(nullable: true)]
    private ?int $parkingCapacity = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $contactDetails = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referenceContactName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $deliveryAccess = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accessMapUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $houseRules = null;

    #[ORM\Embedded(class: Address::class, columnPrefix: 'address_')]
    private ?Address $address = null;

    /**
     * @var Collection<int, Room>
     */
    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: Room::class)]
    private Collection $rooms;

    /**
     * @var Collection<int, VenueDocument>
     */
    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: VenueDocument::class)]
    private Collection $documents;

    /**
     * @var Collection<int, VenueEquipment>
     */
    #[ORM\OneToMany(mappedBy: 'venue', targetEntity: VenueEquipment::class)]
    private Collection $venueEquipments;

    public function __construct()
    {
        $this->publicIdentifier = Uuid::v4()->toRfc4122();
        $this->address = new Address();
        $this->rooms = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->venueEquipments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublicIdentifier(): string
    {
        return $this->publicIdentifier;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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

    public function getPublicTransportAccess(): ?string
    {
        return $this->publicTransportAccess;
    }

    public function setPublicTransportAccess(?string $publicTransportAccess): self
    {
        $this->publicTransportAccess = $publicTransportAccess;

        return $this;
    }

    public function getParkingType(): ?string
    {
        return $this->parkingType;
    }

    public function setParkingType(?string $parkingType): self
    {
        $this->parkingType = $parkingType;

        return $this;
    }

    public function getParkingCapacity(): ?int
    {
        return $this->parkingCapacity;
    }

    public function setParkingCapacity(?int $parkingCapacity): self
    {
        $this->parkingCapacity = $parkingCapacity;

        return $this;
    }

    public function getContactDetails(): ?string
    {
        return $this->contactDetails;
    }

    public function setContactDetails(?string $contactDetails): self
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    public function getReferenceContactName(): ?string
    {
        return $this->referenceContactName;
    }

    public function setReferenceContactName(?string $referenceContactName): self
    {
        $this->referenceContactName = $referenceContactName;

        return $this;
    }

    public function getDeliveryAccess(): ?string
    {
        return $this->deliveryAccess;
    }

    public function setDeliveryAccess(?string $deliveryAccess): self
    {
        $this->deliveryAccess = $deliveryAccess;

        return $this;
    }

    public function getAccessMapUrl(): ?string
    {
        return $this->accessMapUrl;
    }

    public function setAccessMapUrl(?string $accessMapUrl): self
    {
        $this->accessMapUrl = $accessMapUrl;

        return $this;
    }

    public function getHouseRules(): ?string
    {
        return $this->houseRules;
    }

    public function setHouseRules(?string $houseRules): self
    {
        $this->houseRules = $houseRules;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Room>
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms->add($room);
            $room->setVenue($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->removeElement($room)) {
            if ($room->getVenue() === $this) {
                $room->setVenue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VenueDocument>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(VenueDocument $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setVenue($this);
        }

        return $this;
    }

    public function removeDocument(VenueDocument $document): self
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getVenue() === $this) {
                $document->setVenue(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VenueEquipment>
     */
    public function getVenueEquipments(): Collection
    {
        return $this->venueEquipments;
    }

    public function addVenueEquipment(VenueEquipment $venueEquipment): self
    {
        if (!$this->venueEquipments->contains($venueEquipment)) {
            $this->venueEquipments->add($venueEquipment);
            $venueEquipment->setVenue($this);
        }

        return $this;
    }

    public function removeVenueEquipment(VenueEquipment $venueEquipment): self
    {
        if ($this->venueEquipments->removeElement($venueEquipment)) {
            if ($venueEquipment->getVenue() === $this) {
                $venueEquipment->setVenue(null);
            }
        }

        return $this;
    }
}
