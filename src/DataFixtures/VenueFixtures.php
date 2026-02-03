<?php

namespace App\DataFixtures;

use App\Entity\Embeddable\Address;
use App\Entity\EquipmentType;
use App\Entity\Venue;
use App\Entity\VenueDocument;
use App\Entity\VenueEquipment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VenueFixtures extends Fixture implements DependentFixtureInterface
{
    public const VENUE_MAIN = 'venue_main';

    public function load(ObjectManager $manager): void
    {
        $rows = [
            [
                'name' => 'Bairon',
                'line1' => '1 boulevard du Lac',
                'postal_code' => '08390',
                'city' => 'Bairon',
                'capacity' => 180,
                'status' => 'Ouvert',
                'updated_at' => '2026-01-12 09:15:30',
            ],
            [
                'name' => 'Vieilles Forges',
                'line1' => '12 route des Mazures',
                'postal_code' => '08500',
                'city' => 'Les Mazures',
                'capacity' => 200,
                'status' => 'Ouvert',
                'updated_at' => '2026-01-09 14:42:10',
            ],
            [
                'name' => 'Maison des Sports',
                'line1' => 'Rue des Illées',
                'postal_code' => '08140',
                'city' => 'Bazeilles',
                'latitude' => 49.681371500344746,
                'longitude' => 4.986209807039256,
                'capacity' => 260,
                'status' => 'Ouvert',
                'updated_at' => '2026-01-04 18:05:45',
            ],
        ];

        $mainVenue = null;
        foreach ($rows as $row) {
            $address = (new Address())
                ->setLine1($row['line1'])
                ->setPostalCode($row['postal_code'])
                ->setCity($row['city'])
                ->setCountry('FR')
                ->setSource('MANUAL');

            if (isset($row['latitude'])) {
                $address->setLatitude($row['latitude']);
            }
            if (isset($row['longitude'])) {
                $address->setLongitude($row['longitude']);
            }

            $venue = (new Venue())
                ->setName($row['name'])
                ->setDescription(sprintf('Capacité: %d — Statut: %s', $row['capacity'], $row['status']))
                ->setAddress($address);

            $updatedAt = new \DateTime($row['updated_at']);
            $venue->setUpdatedAt($updatedAt);
            $venue->setCreatedAt((clone $updatedAt)->modify('-10 days'));

            if (null === $mainVenue) {
                $mainVenue = $venue;
            }

            if ('Maison des Sports' === $row['name']) {
                $mainVenue = $venue;
            }

            $manager->persist($venue);
        }

        if ($mainVenue instanceof Venue) {
            $this->addReference(self::VENUE_MAIN, $mainVenue);

            $photo = (new VenueDocument())
                ->setVenue($mainVenue)
                ->setLabel('Photo façade')
                ->setFilePath('venues/maison-des-sports/photo.jpg')
                ->setMimeType('image/jpeg')
                ->setType('photo');
            $manager->persist($photo);

            $plan = (new VenueDocument())
                ->setVenue($mainVenue)
                ->setLabel('Plan d\'accès')
                ->setFilePath('venues/maison-des-sports/plan.pdf')
                ->setMimeType('application/pdf')
                ->setType('plan');
            $manager->persist($plan);

            $equipmentType = $this->getReference(ReferenceFixtures::EQUIPMENT_PROJECTOR, EquipmentType::class);
            $venueEquipment = (new VenueEquipment())
                ->setVenue($mainVenue)
                ->setEquipmentType($equipmentType)
                ->setMaxQuantity(2)
                ->setIsIncluded(true);
            $manager->persist($venueEquipment);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ReferenceFixtures::class];
    }
}
