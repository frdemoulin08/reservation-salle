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
    public const ROOM_TYPE_CONFERENCE = 'room_type_conference';
    public const ROOM_TYPE_CATERING = 'room_type_catering';
    public const ROOM_TYPE_RECEPTION = 'room_type_reception';
    public const ROOM_TYPE_MULTI_PURPOSE = 'room_type_multi_purpose';
    public const ROOM_TYPE_SPORTS = 'room_type_sports';
    public const ROOM_TYPE_CULTURAL = 'room_type_cultural';
    public const ROOM_LAYOUT_THEATRE = 'room_layout_theatre';
    public const ROOM_LAYOUT_U = 'room_layout_u';
    public const ROOM_LAYOUT_CLASSROOM = 'room_layout_classroom';
    public const ROOM_LAYOUT_COCKTAIL = 'room_layout_cocktail';
    public const ROOM_LAYOUT_BANQUET = 'room_layout_banquet';
    public const ROOM_LAYOUT_BLEACHERS = 'room_layout_bleachers';
    public const ROOM_LAYOUT_AUDITORIUM = 'room_layout_auditorium';
    public const EQUIPMENT_TECHNICAL = 'equipment_technical';
    public const EQUIPMENT_CATERING = 'equipment_catering';
    public const EQUIPMENT_SPORTS = 'equipment_sports';
    public const EQUIPMENT_STAGE = 'equipment_stage';
    public const SERVICE_CLEANING = 'service_cleaning';
    public const USAGE_TRAINING = 'usage_training';
    public const FEE_CLEANING = 'fee_cleaning';
    public const EVENT_PROFESSIONAL_MEETING = 'event_professional_meeting';
    public const EVENT_TRAINING = 'event_training';
    public const EVENT_CONFERENCE = 'event_conference';
    public const EVENT_ASSOCIATION = 'event_association';
    public const EVENT_SHOW = 'event_show';
    public const EVENT_EXHIBITION = 'event_exhibition';
    public const EVENT_SPORTS = 'event_sports';

    public function load(ObjectManager $manager): void
    {
        $roomTypes = [
            self::ROOM_TYPE_MEETING => ['MEETING_ROOM', 'Salle de réunion'],
            self::ROOM_TYPE_CONFERENCE => ['CONFERENCE_ROOM', 'Salle de conférence'],
            self::ROOM_TYPE_CATERING => ['DINING_ROOM', 'Salle de restauration'],
            self::ROOM_TYPE_RECEPTION => ['RECEPTION_ROOM', 'Salle de réception'],
            self::ROOM_TYPE_MULTI_PURPOSE => ['MULTI_PURPOSE_ROOM', 'Salle polyvalente'],
            self::ROOM_TYPE_SPORTS => ['SPORTS_ROOM', 'Salle sportive'],
            self::ROOM_TYPE_CULTURAL => ['CULTURAL_ROOM', 'Salle culturelle'],
        ];

        foreach ($roomTypes as $reference => [$code, $label]) {
            $roomType = (new RoomType())
                ->setCode($code)
                ->setLabel($label);
            $manager->persist($roomType);
            $this->addReference($reference, $roomType);
        }

        $roomLayouts = [
            self::ROOM_LAYOUT_THEATRE => ['THEATRE_LAYOUT', 'Théâtre'],
            self::ROOM_LAYOUT_U => ['U_SHAPE_LAYOUT', 'U'],
            self::ROOM_LAYOUT_CLASSROOM => ['CLASSROOM_LAYOUT', 'Classe'],
            self::ROOM_LAYOUT_COCKTAIL => ['COCKTAIL_LAYOUT', 'Cocktail'],
            self::ROOM_LAYOUT_BANQUET => ['BANQUET_LAYOUT', 'Banquet'],
            self::ROOM_LAYOUT_BLEACHERS => ['BLEACHERS_LAYOUT', 'Gradin'],
            self::ROOM_LAYOUT_AUDITORIUM => ['AUDITORIUM_LAYOUT', 'Auditorium'],
        ];

        foreach ($roomLayouts as $reference => [$code, $label]) {
            $roomLayout = (new RoomLayout())
                ->setCode($code)
                ->setLabel($label);
            $manager->persist($roomLayout);
            $this->addReference($reference, $roomLayout);
        }

        $equipmentTypes = [
            self::EQUIPMENT_TECHNICAL => ['TECHNICAL_EQUIPMENT', 'Technique', 'technical'],
            self::EQUIPMENT_CATERING => ['CATERING_EQUIPMENT', 'Restauration', 'catering'],
            self::EQUIPMENT_SPORTS => ['SPORTS_EQUIPMENT', 'Sportif', 'sports'],
            self::EQUIPMENT_STAGE => ['STAGE_EQUIPMENT', 'Scénique', 'stage'],
        ];

        foreach ($equipmentTypes as $reference => [$code, $label, $category]) {
            $equipmentType = (new EquipmentType())
                ->setCode($code)
                ->setLabel($label)
                ->setCategory($category);
            $manager->persist($equipmentType);
            $this->addReference($reference, $equipmentType);
        }

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

        $eventTypes = [
            self::EVENT_PROFESSIONAL_MEETING => ['PROFESSIONAL_MEETING_EVENT', 'Réunion professionnelle'],
            self::EVENT_TRAINING => ['TRAINING_EVENT', 'Formation'],
            self::EVENT_CONFERENCE => ['CONFERENCE_EVENT', 'Conférence'],
            self::EVENT_ASSOCIATION => ['ASSOCIATION_EVENT', 'Événement associatif'],
            self::EVENT_SHOW => ['SHOW_EVENT', 'Spectacle'],
            self::EVENT_EXHIBITION => ['EXHIBITION_EVENT', 'Exposition'],
            self::EVENT_SPORTS => ['SPORTS_EVENT', 'Manifestation sportive'],
        ];

        foreach ($eventTypes as $reference => [$code, $label]) {
            $eventType = (new EventType())
                ->setCode($code)
                ->setLabel($label);
            $manager->persist($eventType);
            $this->addReference($reference, $eventType);
        }

        $manager->flush();
    }
}
