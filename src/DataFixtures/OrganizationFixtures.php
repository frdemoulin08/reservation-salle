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
    public const ORG_SPORT = 'org_sport';
    public const ORG_ENTREPRISE = 'org_entreprise';
    public const ORG_COLLECTIVITE = 'org_collectivite';
    public const ORG_CD08 = 'org_cd08';
    public const ORG_LYCEE = 'org_lycee';
    public const ORG_PHOTO = 'org_photo';
    public const ORG_MJC = 'org_mjc';
    public const ORG_TECH = 'org_tech';
    public const ORG_THEATRE = 'org_theatre';
    public const ORG_TOURISME = 'org_tourisme';

    public function load(ObjectManager $manager): void
    {
        $headOffice = $this->createAddress('10 rue de la République', '08000', 'Charleville-Mézières');
        $billing = $this->createAddress('10 rue de la République', '08000', 'Charleville-Mézières');

        $organization = (new Organization())
            ->setSiret('10000000000008')
            ->setLegalName('Association Exemple')
            ->setDisplayName('Association Exemple')
            ->setLegalNature('Association loi 1901')
            ->setOrganizationType('ASSOCIATION')
            ->setAssociationRegistered(true)
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

        $extraOrganizations = [
            [
                'key' => self::ORG_SPORT,
                'legalName' => 'Association Sportive Ardennes',
                'displayName' => 'ASA',
                'legalNature' => 'Association loi 1901',
                'organizationType' => 'ASSOCIATION',
                'siret' => '10000000000016',
                'headOffice' => ['1 avenue des Sports', '08000', 'Charleville-Mézières'],
                'billing' => ['1 avenue des Sports', '08000', 'Charleville-Mézières'],
            ],
            [
                'key' => self::ORG_ENTREPRISE,
                'legalName' => 'Dupont Événementiel SARL',
                'displayName' => 'Dupont Événementiel',
                'legalNature' => 'SARL',
                'organizationType' => 'ENTREPRISE',
                'siret' => '10000000000024',
                'headOffice' => ['12 rue des Tilleuls', '08200', 'Sedan'],
                'billing' => ['3 rue du Commerce', '08200', 'Sedan'],
            ],
            [
                'key' => self::ORG_COLLECTIVITE,
                'legalName' => 'Commune de Sedan',
                'displayName' => 'Mairie de Sedan',
                'legalNature' => 'Commune',
                'organizationType' => 'COLLECTIVITE',
                'siret' => '10000000000032',
                'headOffice' => ['Place d’Armes', '08200', 'Sedan'],
                'billing' => ['Place d’Armes', '08200', 'Sedan'],
            ],
            [
                'key' => self::ORG_CD08,
                'legalName' => 'CD08 - Direction de la Culture',
                'displayName' => 'CD08 Culture',
                'legalNature' => 'Conseil départemental des Ardennes',
                'organizationType' => 'CD08_SERVICE',
                'siret' => '10000000000040',
                'headOffice' => ['19 rue de l’Arquebuse', '08000', 'Charleville-Mézières'],
                'billing' => ['19 rue de l’Arquebuse', '08000', 'Charleville-Mézières'],
            ],
            [
                'key' => self::ORG_LYCEE,
                'legalName' => 'Lycée Sévigné',
                'displayName' => 'Lycée Sévigné',
                'legalNature' => 'Établissement public',
                'organizationType' => 'AUTRE',
                'siret' => '10000000000057',
                'headOffice' => ['5 boulevard Victor Hugo', '08000', 'Charleville-Mézières'],
                'billing' => ['5 boulevard Victor Hugo', '08000', 'Charleville-Mézières'],
            ],
            [
                'key' => self::ORG_PHOTO,
                'legalName' => 'Club Photo 08',
                'displayName' => 'Club Photo 08',
                'legalNature' => 'Association loi 1901',
                'organizationType' => 'ASSOCIATION',
                'siret' => '10000000000065',
                'headOffice' => ['22 rue des Arts', '08110', 'Carignan'],
                'billing' => ['22 rue des Arts', '08110', 'Carignan'],
            ],
            [
                'key' => self::ORG_MJC,
                'legalName' => 'Maison des Jeunes et de la Culture',
                'displayName' => 'MJC Nouzonville',
                'legalNature' => 'Association loi 1901',
                'organizationType' => 'ASSOCIATION',
                'siret' => '10000000000073',
                'headOffice' => ['8 rue de la Gare', '08700', 'Nouzonville'],
                'billing' => ['8 rue de la Gare', '08700', 'Nouzonville'],
            ],
            [
                'key' => self::ORG_TECH,
                'legalName' => 'Techno Ardennes SAS',
                'displayName' => 'Techno Ardennes',
                'legalNature' => 'SAS',
                'organizationType' => 'ENTREPRISE',
                'siret' => '10000000000081',
                'headOffice' => ['14 rue des Forges', '08100', 'Villers-Semeuse'],
                'billing' => ['14 rue des Forges', '08100', 'Villers-Semeuse'],
            ],
            [
                'key' => self::ORG_THEATRE,
                'legalName' => 'Compagnie Théâtre des Ardennes',
                'displayName' => 'Théâtre des Ardennes',
                'legalNature' => 'Association loi 1901',
                'organizationType' => 'ASSOCIATION',
                'siret' => '10000000000099',
                'headOffice' => ['9 place du Théâtre', '08000', 'Charleville-Mézières'],
                'billing' => ['9 place du Théâtre', '08000', 'Charleville-Mézières'],
            ],
            [
                'key' => self::ORG_TOURISME,
                'legalName' => 'Office de Tourisme des Crêtes',
                'displayName' => 'Tourisme des Crêtes',
                'legalNature' => 'Association loi 1901',
                'organizationType' => 'ASSOCIATION',
                'siret' => '10000000000107',
                'headOffice' => ['2 rue du Panorama', '08600', 'Givet'],
                'billing' => ['2 rue du Panorama', '08600', 'Givet'],
            ],
        ];

        $contactFirstNames = ['Julie', 'Hugo', 'Emma', 'Nicolas', 'Camille', 'Lucas', 'Chloé', 'Pierre', 'Sarah', 'Thomas'];
        $contactLastNames = ['Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit', 'Durand', 'Leroy', 'Moreau'];
        $contactTitles = ['Mme', 'M.'];

        $index = 0;
        foreach ($extraOrganizations as $data) {
            $headOffice = $this->createAddress($data['headOffice'][0], $data['headOffice'][1], $data['headOffice'][2]);
            $billing = $this->createAddress($data['billing'][0], $data['billing'][1], $data['billing'][2]);

            $organization = (new Organization())
                ->setSiret($data['siret'])
                ->setLegalName($data['legalName'])
                ->setDisplayName($data['displayName'])
                ->setLegalNature($data['legalNature'])
                ->setOrganizationType($data['organizationType'])
                ->setAssociationRegistered('ASSOCIATION' === $data['organizationType'])
                ->setBillingSameAsHeadOffice($data['headOffice'] === $data['billing'])
                ->setHeadOfficeAddress($headOffice)
                ->setBillingAddress($billing);

            $createdAt = new \DateTime('2026-01-05 09:00:00');
            $createdAt->modify(sprintf('+%d days', $index + 1));
            $organization->setCreatedAt(clone $createdAt);
            $organization->setUpdatedAt((clone $createdAt)->modify('+2 days'));

            $manager->persist($organization);
            $this->addReference($data['key'], $organization);

            $firstName = $contactFirstNames[$index % count($contactFirstNames)];
            $lastName = $contactLastNames[$index % count($contactLastNames)];
            $title = $contactTitles[$index % count($contactTitles)];
            $emailDomain = $this->slugify($data['displayName']);
            if ('' === $emailDomain) {
                $emailDomain = sprintf('org%d', $index + 1);
            }

            $contact = (new OrganizationContact())
                ->setOrganization($organization)
                ->setRole('CONTACT')
                ->setTitle($title)
                ->setJobTitle('Responsable')
                ->setFirstName($firstName)
                ->setLastName($lastName)
                ->setEmail(sprintf('%s.%s@%s.example.org', $this->slugify($firstName), $this->slugify($lastName), $emailDomain))
                ->setPhone(sprintf('06%02d%02d%02d%02d', $index + 1, $index + 2, $index + 3, $index + 4));
            $manager->persist($contact);

            ++$index;
        }

        $manager->flush();
    }

    private function createAddress(string $line1, string $postalCode, string $city, string $country = 'FR'): Address
    {
        return (new Address())
            ->setLine1($line1)
            ->setPostalCode($postalCode)
            ->setCity($city)
            ->setCountry($country)
            ->setSource('MANUAL');
    }

    private function slugify(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        if (false !== $normalized) {
            $value = $normalized;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value;
    }
}
