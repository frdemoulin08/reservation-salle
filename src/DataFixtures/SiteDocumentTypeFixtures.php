<?php

namespace App\DataFixtures;

use App\Entity\SiteDocumentType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteDocumentTypeFixtures extends Fixture
{
    public const TYPE_PHOTO = 'site_document_type_photo';
    public const TYPE_PLAN = 'site_document_type_plan';
    public const TYPE_OTHER = 'site_document_type_other';

    public function load(ObjectManager $manager): void
    {
        $photo = (new SiteDocumentType())
            ->setCode(SiteDocumentType::CODE_PHOTO)
            ->setLabel('Photo')
            ->setDescription('Photo du site')
            ->setIsPublic(true)
            ->setIsRequired(false)
            ->setIsMultipleAllowed(true)
            ->setIsActive(true)
            ->setPosition(1);
        $manager->persist($photo);
        $this->addReference(self::TYPE_PHOTO, $photo);

        $plan = (new SiteDocumentType())
            ->setCode(SiteDocumentType::CODE_PLAN)
            ->setLabel('Plan d\'accès')
            ->setDescription('Plan d\'accès ou localisation')
            ->setIsPublic(true)
            ->setIsRequired(false)
            ->setIsMultipleAllowed(false)
            ->setIsActive(true)
            ->setPosition(2);
        $manager->persist($plan);
        $this->addReference(self::TYPE_PLAN, $plan);

        $other = (new SiteDocumentType())
            ->setCode(SiteDocumentType::CODE_OTHER)
            ->setLabel('Autre document')
            ->setDescription('Document spécifique au site')
            ->setIsPublic(false)
            ->setIsRequired(false)
            ->setIsMultipleAllowed(true)
            ->setIsActive(true)
            ->setPosition(3);
        $manager->persist($other);
        $this->addReference(self::TYPE_OTHER, $other);

        $manager->flush();
    }
}
