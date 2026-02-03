<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CountryFixtures extends Fixture
{
    public const FR = 'country_fr';
    public const BE = 'country_be';
    public const LU = 'country_lu';
    public const DE = 'country_de';

    public function load(ObjectManager $manager): void
    {
        $rows = [
            ['code' => 'FR', 'label' => 'France', 'dialing_code' => '+33'],
            ['code' => 'BE', 'label' => 'Belgique', 'dialing_code' => '+32'],
            ['code' => 'LU', 'label' => 'Luxembourg', 'dialing_code' => '+352'],
            ['code' => 'DE', 'label' => 'Allemagne', 'dialing_code' => '+49'],
        ];

        foreach ($rows as $row) {
            $country = (new Country())
                ->setCode($row['code'])
                ->setLabel($row['label'])
                ->setDialingCode($row['dialing_code'])
                ->setIsActive(true);

            $manager->persist($country);
            $this->addReference('country_'.$row['code'], $country);
        }

        $manager->flush();
    }
}
