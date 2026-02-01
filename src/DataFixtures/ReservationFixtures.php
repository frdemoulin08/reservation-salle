<?php

namespace App\DataFixtures;

use App\Entity\AdditionalFeeType;
use App\Entity\EventType;
use App\Entity\Organization;
use App\Entity\OrganizationContact;
use App\Entity\Reservation;
use App\Entity\ReservationAdditionalFee;
use App\Entity\Room;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $room = $this->getReference(RoomFixtures::ROOM_MAIN, Room::class);
        $organization = $this->getReference(OrganizationFixtures::ORG_MAIN, Organization::class);
        $contact = $this->getReference(OrganizationFixtures::CONTACT_REQUESTER, OrganizationContact::class);
        $eventType = $this->getReference(ReferenceFixtures::EVENT_CONFERENCE, EventType::class);

        $reservation = (new Reservation())
            ->setRoom($room)
            ->setOrganization($organization)
            ->setOrganizationContact($contact)
            ->setEventType($eventType)
            ->setStartDate(new \DateTimeImmutable('2026-03-10 09:00:00'))
            ->setEndDate(new \DateTimeImmutable('2026-03-10 17:00:00'))
            ->setStatus('CONFIRMED')
            ->setTicketingType('NONE')
            ->setSecurityDeposit('250.00')
            ->setComment('Réservation test pour conférence.');

        $reservation->setCreatedAt(new \DateTime('2026-01-25 10:30:00'));
        $reservation->setUpdatedAt(new \DateTime('2026-01-26 09:10:00'));

        $manager->persist($reservation);

        $feeType = $this->getReference(ReferenceFixtures::FEE_CLEANING, AdditionalFeeType::class);
        $additionalFee = (new ReservationAdditionalFee())
            ->setReservation($reservation)
            ->setAdditionalFeeType($feeType)
            ->setAmount('50.00')
            ->setLabel('Ménage après événement');
        $manager->persist($additionalFee);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ReferenceFixtures::class, OrganizationFixtures::class, RoomFixtures::class];
    }
}
