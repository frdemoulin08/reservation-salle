<?php

namespace App\DataFixtures;

use App\Entity\Embeddable\Address;
use App\Entity\Organization;
use App\Entity\OrganizationContact;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OrganizationFixtures extends Fixture
{
    public const ORG_MAIN = 'org_main';
    public const CONTACT_REQUESTER = 'contact_requester';
    public const CONTACT_PAYER = 'contact_payer';

    public function load(ObjectManager $manager): void
    {
        $headOffice = (new Address())
            ->setLine1('10 rue de la République')
            ->setPostalCode('08000')
            ->setCity('Charleville-Mézières')
            ->setCountry('FR')
            ->setSource('MANUAL');

        $billing = (new Address())
            ->setLine1('10 rue de la République')
            ->setPostalCode('08000')
            ->setCity('Charleville-Mézières')
            ->setCountry('FR')
            ->setSource('MANUAL');

        $organization = (new Organization())
            ->setSiret('12345678901234')
            ->setLegalName('Association Exemple')
            ->setDisplayName('Association Exemple')
            ->setLegalNature('Association')
            ->setOrganizationType('private')
            ->setBillingSameAsHeadOffice(true)
            ->setHeadOfficeAddress($headOffice)
            ->setBillingAddress($billing);

        $organization->setCreatedAt(new \DateTime('2026-01-20 09:00:00'));
        $organization->setUpdatedAt(new \DateTime('2026-01-25 10:00:00'));

        $manager->persist($organization);
        $this->addReference(self::ORG_MAIN, $organization);

        $requester = (new OrganizationContact())
            ->setOrganization($organization)
            ->setRole('REQUESTER')
            ->setTitle('Mme')
            ->setJobTitle('Chargée de projet')
            ->setFirstName('Claire')
            ->setLastName('Dupont')
            ->setEmail('claire.dupont@example.org')
            ->setPhone('0611223344');
        $manager->persist($requester);
        $this->addReference(self::CONTACT_REQUESTER, $requester);

        $payer = (new OrganizationContact())
            ->setOrganization($organization)
            ->setRole('PAYER')
            ->setTitle('M.')
            ->setJobTitle('Trésorier')
            ->setFirstName('Marc')
            ->setLastName('Leroy')
            ->setEmail('marc.leroy@example.org')
            ->setPhone('0677889900');
        $manager->persist($payer);
        $this->addReference(self::CONTACT_PAYER, $payer);

        $manager->flush();
    }
}
