<?php

namespace App\DataFixtures;

use App\Entity\EquipmentType;
use App\Entity\Room;
use App\Entity\RoomEquipment;
use App\Entity\RoomLayout;
use App\Entity\RoomPricing;
use App\Entity\RoomService;
use App\Entity\RoomType;
use App\Entity\ServiceType;
use App\Entity\Venue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RoomFixtures extends Fixture implements DependentFixtureInterface
{
    public const ROOM_MAIN = 'room_main';

    public function load(ObjectManager $manager): void
    {
        $venue = $this->getReference(VenueFixtures::VENUE_MAIN, Venue::class);

        $room = (new Room())
            ->setVenue($venue)
            ->setName('Salle du Conseil')
            ->setDescription('Salle polyvalente pour réunions et formations.')
            ->setSurfaceArea('120.00')
            ->setSeatedCapacity(80)
            ->setStandingCapacity(120)
            ->setIsPmrAccessible(true)
            ->setHasElevator(true)
            ->setHasPmrRestrooms(true)
            ->setHasEmergencyExits(true)
            ->setIsErpCompliant(true)
            ->setErpType('L')
            ->setErpCategory('3')
            ->setSecurityStaffRequired(false)
            ->setOpeningHoursSchema('Lun-Ven: 08:00-22:00')
            ->setMinRentalDurationMinutes(120)
            ->setMaxRentalDurationMinutes(480)
            ->setBookingLeadTimeDays(7)
            ->setCateringAllowed(true)
            ->setAlcoholAllowed(false)
            ->setAlcoholLegalNotice(null)
            ->setMusicAllowed(true)
            ->setSacemRequired(true);

        $room->setCreatedAt(new \DateTime('2026-01-18 09:00:00'));
        $room->setUpdatedAt(new \DateTime('2026-01-25 11:00:00'));

        $room->addRoomType($this->getReference(ReferenceFixtures::ROOM_TYPE_MEETING, RoomType::class));
        $room->addRoomLayout($this->getReference(ReferenceFixtures::ROOM_LAYOUT_THEATRE, RoomLayout::class));

        $manager->persist($room);
        $this->addReference(self::ROOM_MAIN, $room);

        $equipmentType = $this->getReference(ReferenceFixtures::EQUIPMENT_TECHNICAL, EquipmentType::class);
        $roomEquipment = (new RoomEquipment())
            ->setRoom($room)
            ->setEquipmentType($equipmentType)
            ->setMaxQuantity(1)
            ->setExclusiveToRoom(true)
            ->setIsIncluded(true);
        $manager->persist($roomEquipment);

        $serviceType = $this->getReference(ReferenceFixtures::SERVICE_CLEANING, ServiceType::class);
        $roomService = (new RoomService())
            ->setRoom($room)
            ->setServiceType($serviceType)
            ->setIsIncluded(true);
        $manager->persist($roomService);

        $pricing = (new RoomPricing())
            ->setRoom($room)
            ->setPriceCategory('ASSOCIATION')
            ->setHourlyRate('25.00')
            ->setDailyRate('180.00')
            ->setCurrency('EUR');
        $manager->persist($pricing);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ReferenceFixtures::class, VenueFixtures::class];
    }
}
