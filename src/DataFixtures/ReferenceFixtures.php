<?php

namespace App\DataFixtures;

use App\Entity\AdditionalFeeType;
use App\Entity\EquipmentType;
use App\Entity\EventType;
use App\Entity\RoomLayout;
use App\Entity\RoomType;
use App\Entity\ServiceType;
use App\Entity\UsageType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ReferenceFixtures extends Fixture
{
    public const ROOM_TYPE_MEETING = 'room_type_meeting';
    public const ROOM_LAYOUT_THEATRE = 'room_layout_theatre';
    public const EQUIPMENT_PROJECTOR = 'equipment_projector';
    public const SERVICE_CLEANING = 'service_cleaning';
    public const USAGE_TRAINING = 'usage_training';
    public const FEE_CLEANING = 'fee_cleaning';
    public const EVENT_CONFERENCE = 'event_conference';

    public function load(ObjectManager $manager): void
    {
        $roomType = (new RoomType())
            ->setCode('meeting')
            ->setLabel('Réunion');
        $manager->persist($roomType);
        $this->addReference(self::ROOM_TYPE_MEETING, $roomType);

        $roomLayout = (new RoomLayout())
            ->setCode('theatre')
            ->setLabel('Théâtre');
        $manager->persist($roomLayout);
        $this->addReference(self::ROOM_LAYOUT_THEATRE, $roomLayout);

        $equipmentType = (new EquipmentType())
            ->setCode('projector')
            ->setLabel('Projecteur')
            ->setCategory('technical');
        $manager->persist($equipmentType);
        $this->addReference(self::EQUIPMENT_PROJECTOR, $equipmentType);

        $serviceType = (new ServiceType())
            ->setCode('cleaning')
            ->setLabel('Ménage');
        $manager->persist($serviceType);
        $this->addReference(self::SERVICE_CLEANING, $serviceType);

        $usageType = (new UsageType())
            ->setCode('training')
            ->setLabel('Formations');
        $manager->persist($usageType);
        $this->addReference(self::USAGE_TRAINING, $usageType);

        $additionalFeeType = (new AdditionalFeeType())
            ->setCode('cleaning')
            ->setLabel('Ménage');
        $manager->persist($additionalFeeType);
        $this->addReference(self::FEE_CLEANING, $additionalFeeType);

        $eventType = (new EventType())
            ->setCode('conference')
            ->setLabel('Conférence');
        $manager->persist($eventType);
        $this->addReference(self::EVENT_CONFERENCE, $eventType);

        $manager->flush();
    }
}
