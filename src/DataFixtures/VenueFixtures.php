<?php

namespace App\DataFixtures;

use App\Entity\Venue;
use App\Entity\EquipmentType;
use App\Entity\VenueDocument;
use App\Entity\VenueEquipment;
use App\Entity\Embeddable\Address;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class VenueFixtures extends Fixture implements DependentFixtureInterface
{
    public const VENUE_MAIN = 'venue_main';

    public function load(ObjectManager $manager): void
    {
        $address = (new Address())
            ->setLine1('2 place Ducale')
            ->setPostalCode('08000')
            ->setCity('Charleville-Mézières')
            ->setCountry('FR')
            ->setSource('MANUAL');

        $venue = (new Venue())
            ->setName('Maison des Associations')
            ->setDescription('Site principal pour les réunions et conférences.')
            ->setPublicTransportAccess('Bus ligne 1, arrêt Hôtel de Ville')
            ->setParkingType('gratuit')
            ->setParkingCapacity(40)
            ->setContactDetails('contact@exemple.org / 03 24 00 00 00')
            ->setReferenceContactName('Sophie Martin')
            ->setDeliveryAccess('Entrée livraison par rue des Faubourgs')
            ->setAccessMapUrl('https://maps.example.org/maison-associations')
            ->setHouseRules('Respect du voisinage, pas de bruit après 22h.')
            ->setAddress($address);

        $venue->setCreatedAt(new \DateTime('2026-01-15 08:00:00'));
        $venue->setUpdatedAt(new \DateTime('2026-01-25 10:30:00'));

        $manager->persist($venue);
        $this->addReference(self::VENUE_MAIN, $venue);

        $photo = (new VenueDocument())
            ->setVenue($venue)
            ->setLabel('Photo façade')
            ->setFilePath('venues/maison-associations/photo.jpg')
            ->setMimeType('image/jpeg')
            ->setType('photo');
        $manager->persist($photo);

        $plan = (new VenueDocument())
            ->setVenue($venue)
            ->setLabel('Plan d\'accès')
            ->setFilePath('venues/maison-associations/plan.pdf')
            ->setMimeType('application/pdf')
            ->setType('plan');
        $manager->persist($plan);

        $equipmentType = $this->getReference(ReferenceFixtures::EQUIPMENT_PROJECTOR, EquipmentType::class);
        $venueEquipment = (new VenueEquipment())
            ->setVenue($venue)
            ->setEquipmentType($equipmentType)
            ->setMaxQuantity(2)
            ->setIsIncluded(true);
        $manager->persist($venueEquipment);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ReferenceFixtures::class];
    }
}
