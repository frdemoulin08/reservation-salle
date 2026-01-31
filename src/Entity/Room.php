<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity]
class Room
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rooms')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Venue $venue = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $surfaceArea = null;

    #[ORM\Column(nullable: true)]
    private ?int $seatedCapacity = null;

    #[ORM\Column(nullable: true)]
    private ?int $standingCapacity = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $isPmrAccessible = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasElevator = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasPmrRestrooms = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $hasEmergencyExits = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $isErpCompliant = false;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $erpType = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $erpCategory = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $securityStaffRequired = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $openingHoursSchema = null;

    #[ORM\Column(nullable: true)]
    private ?int $minRentalDurationMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $maxRentalDurationMinutes = null;

    #[ORM\Column(nullable: true)]
    private ?int $bookingLeadTimeDays = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $cateringAllowed = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $alcoholAllowed = false;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $alcoholLegalNotice = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $musicAllowed = false;

    #[ORM\Column(options: ['default' => false])]
    private bool $sacemRequired = false;

    /**
     * @var Collection<int, RoomType>
     */
    #[ORM\ManyToMany(targetEntity: RoomType::class, inversedBy: 'rooms')]
    private Collection $roomTypes;

    /**
     * @var Collection<int, RoomLayout>
     */
    #[ORM\ManyToMany(targetEntity: RoomLayout::class, inversedBy: 'rooms')]
    private Collection $roomLayouts;

    /**
     * @var Collection<int, RoomEquipment>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomEquipment::class)]
    private Collection $roomEquipments;

    /**
     * @var Collection<int, RoomService>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomService::class)]
    private Collection $roomServices;

    /**
     * @var Collection<int, RoomUsage>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomUsage::class)]
    private Collection $roomUsages;

    /**
     * @var Collection<int, RoomDocument>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomDocument::class)]
    private Collection $roomDocuments;

    /**
     * @var Collection<int, RoomPricing>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: RoomPricing::class)]
    private Collection $roomPricings;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(mappedBy: 'room', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->roomTypes = new ArrayCollection();
        $this->roomLayouts = new ArrayCollection();
        $this->roomEquipments = new ArrayCollection();
        $this->roomServices = new ArrayCollection();
        $this->roomUsages = new ArrayCollection();
        $this->roomDocuments = new ArrayCollection();
        $this->roomPricings = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

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

    public function getSurfaceArea(): ?string
    {
        return $this->surfaceArea;
    }

    public function setSurfaceArea(?string $surfaceArea): self
    {
        $this->surfaceArea = $surfaceArea;

        return $this;
    }

    public function getSeatedCapacity(): ?int
    {
        return $this->seatedCapacity;
    }

    public function setSeatedCapacity(?int $seatedCapacity): self
    {
        $this->seatedCapacity = $seatedCapacity;

        return $this;
    }

    public function getStandingCapacity(): ?int
    {
        return $this->standingCapacity;
    }

    public function setStandingCapacity(?int $standingCapacity): self
    {
        $this->standingCapacity = $standingCapacity;

        return $this;
    }

    public function isPmrAccessible(): bool
    {
        return $this->isPmrAccessible;
    }

    public function setIsPmrAccessible(bool $isPmrAccessible): self
    {
        $this->isPmrAccessible = $isPmrAccessible;

        return $this;
    }

    public function hasElevator(): bool
    {
        return $this->hasElevator;
    }

    public function setHasElevator(bool $hasElevator): self
    {
        $this->hasElevator = $hasElevator;

        return $this;
    }

    public function hasPmrRestrooms(): bool
    {
        return $this->hasPmrRestrooms;
    }

    public function setHasPmrRestrooms(bool $hasPmrRestrooms): self
    {
        $this->hasPmrRestrooms = $hasPmrRestrooms;

        return $this;
    }

    public function hasEmergencyExits(): bool
    {
        return $this->hasEmergencyExits;
    }

    public function setHasEmergencyExits(bool $hasEmergencyExits): self
    {
        $this->hasEmergencyExits = $hasEmergencyExits;

        return $this;
    }

    public function isErpCompliant(): bool
    {
        return $this->isErpCompliant;
    }

    public function setIsErpCompliant(bool $isErpCompliant): self
    {
        $this->isErpCompliant = $isErpCompliant;

        return $this;
    }

    public function getErpType(): ?string
    {
        return $this->erpType;
    }

    public function setErpType(?string $erpType): self
    {
        $this->erpType = $erpType;

        return $this;
    }

    public function getErpCategory(): ?string
    {
        return $this->erpCategory;
    }

    public function setErpCategory(?string $erpCategory): self
    {
        $this->erpCategory = $erpCategory;

        return $this;
    }

    public function isSecurityStaffRequired(): bool
    {
        return $this->securityStaffRequired;
    }

    public function setSecurityStaffRequired(bool $securityStaffRequired): self
    {
        $this->securityStaffRequired = $securityStaffRequired;

        return $this;
    }

    public function getOpeningHoursSchema(): ?string
    {
        return $this->openingHoursSchema;
    }

    public function setOpeningHoursSchema(?string $openingHoursSchema): self
    {
        $this->openingHoursSchema = $openingHoursSchema;

        return $this;
    }

    public function getMinRentalDurationMinutes(): ?int
    {
        return $this->minRentalDurationMinutes;
    }

    public function setMinRentalDurationMinutes(?int $minRentalDurationMinutes): self
    {
        $this->minRentalDurationMinutes = $minRentalDurationMinutes;

        return $this;
    }

    public function getMaxRentalDurationMinutes(): ?int
    {
        return $this->maxRentalDurationMinutes;
    }

    public function setMaxRentalDurationMinutes(?int $maxRentalDurationMinutes): self
    {
        $this->maxRentalDurationMinutes = $maxRentalDurationMinutes;

        return $this;
    }

    public function getBookingLeadTimeDays(): ?int
    {
        return $this->bookingLeadTimeDays;
    }

    public function setBookingLeadTimeDays(?int $bookingLeadTimeDays): self
    {
        $this->bookingLeadTimeDays = $bookingLeadTimeDays;

        return $this;
    }

    public function isCateringAllowed(): bool
    {
        return $this->cateringAllowed;
    }

    public function setCateringAllowed(bool $cateringAllowed): self
    {
        $this->cateringAllowed = $cateringAllowed;

        return $this;
    }

    public function isAlcoholAllowed(): bool
    {
        return $this->alcoholAllowed;
    }

    public function setAlcoholAllowed(bool $alcoholAllowed): self
    {
        $this->alcoholAllowed = $alcoholAllowed;

        return $this;
    }

    public function getAlcoholLegalNotice(): ?string
    {
        return $this->alcoholLegalNotice;
    }

    public function setAlcoholLegalNotice(?string $alcoholLegalNotice): self
    {
        $this->alcoholLegalNotice = $alcoholLegalNotice;

        return $this;
    }

    public function isMusicAllowed(): bool
    {
        return $this->musicAllowed;
    }

    public function setMusicAllowed(bool $musicAllowed): self
    {
        $this->musicAllowed = $musicAllowed;

        return $this;
    }

    public function isSacemRequired(): bool
    {
        return $this->sacemRequired;
    }

    public function setSacemRequired(bool $sacemRequired): self
    {
        $this->sacemRequired = $sacemRequired;

        return $this;
    }

    /**
     * @return Collection<int, RoomType>
     */
    public function getRoomTypes(): Collection
    {
        return $this->roomTypes;
    }

    public function addRoomType(RoomType $roomType): self
    {
        if (!$this->roomTypes->contains($roomType)) {
            $this->roomTypes->add($roomType);
        }

        return $this;
    }

    public function removeRoomType(RoomType $roomType): self
    {
        $this->roomTypes->removeElement($roomType);

        return $this;
    }

    /**
     * @return Collection<int, RoomLayout>
     */
    public function getRoomLayouts(): Collection
    {
        return $this->roomLayouts;
    }

    public function addRoomLayout(RoomLayout $roomLayout): self
    {
        if (!$this->roomLayouts->contains($roomLayout)) {
            $this->roomLayouts->add($roomLayout);
        }

        return $this;
    }

    public function removeRoomLayout(RoomLayout $roomLayout): self
    {
        $this->roomLayouts->removeElement($roomLayout);

        return $this;
    }

    /**
     * @return Collection<int, RoomEquipment>
     */
    public function getRoomEquipments(): Collection
    {
        return $this->roomEquipments;
    }

    public function addRoomEquipment(RoomEquipment $roomEquipment): self
    {
        if (!$this->roomEquipments->contains($roomEquipment)) {
            $this->roomEquipments->add($roomEquipment);
            $roomEquipment->setRoom($this);
        }

        return $this;
    }

    public function removeRoomEquipment(RoomEquipment $roomEquipment): self
    {
        if ($this->roomEquipments->removeElement($roomEquipment)) {
            if ($roomEquipment->getRoom() === $this) {
                $roomEquipment->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RoomService>
     */
    public function getRoomServices(): Collection
    {
        return $this->roomServices;
    }

    public function addRoomService(RoomService $roomService): self
    {
        if (!$this->roomServices->contains($roomService)) {
            $this->roomServices->add($roomService);
            $roomService->setRoom($this);
        }

        return $this;
    }

    public function removeRoomService(RoomService $roomService): self
    {
        if ($this->roomServices->removeElement($roomService)) {
            if ($roomService->getRoom() === $this) {
                $roomService->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RoomUsage>
     */
    public function getRoomUsages(): Collection
    {
        return $this->roomUsages;
    }

    public function addRoomUsage(RoomUsage $roomUsage): self
    {
        if (!$this->roomUsages->contains($roomUsage)) {
            $this->roomUsages->add($roomUsage);
            $roomUsage->setRoom($this);
        }

        return $this;
    }

    public function removeRoomUsage(RoomUsage $roomUsage): self
    {
        if ($this->roomUsages->removeElement($roomUsage)) {
            if ($roomUsage->getRoom() === $this) {
                $roomUsage->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RoomDocument>
     */
    public function getRoomDocuments(): Collection
    {
        return $this->roomDocuments;
    }

    public function addRoomDocument(RoomDocument $roomDocument): self
    {
        if (!$this->roomDocuments->contains($roomDocument)) {
            $this->roomDocuments->add($roomDocument);
            $roomDocument->setRoom($this);
        }

        return $this;
    }

    public function removeRoomDocument(RoomDocument $roomDocument): self
    {
        if ($this->roomDocuments->removeElement($roomDocument)) {
            if ($roomDocument->getRoom() === $this) {
                $roomDocument->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, RoomPricing>
     */
    public function getRoomPricings(): Collection
    {
        return $this->roomPricings;
    }

    public function addRoomPricing(RoomPricing $roomPricing): self
    {
        if (!$this->roomPricings->contains($roomPricing)) {
            $this->roomPricings->add($roomPricing);
            $roomPricing->setRoom($this);
        }

        return $this;
    }

    public function removeRoomPricing(RoomPricing $roomPricing): self
    {
        if ($this->roomPricings->removeElement($roomPricing)) {
            if ($roomPricing->getRoom() === $this) {
                $roomPricing->setRoom(null);
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
            $reservation->setRoom($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            if ($reservation->getRoom() === $this) {
                $reservation->setRoom(null);
            }
        }

        return $this;
    }
}
