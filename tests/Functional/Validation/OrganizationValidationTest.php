<?php

namespace App\Tests\Functional\Validation;

use App\Entity\Organization;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizationValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testSiretRequiredForFrenchEntreprise(): void
    {
        $organization = $this->createOrganization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->getHeadOfficeAddress()?->setCountry('FR');

        $violations = $this->validator->validate($organization);

        self::assertCount(1, $violations);
        self::assertSame('siret', $violations[0]->getPropertyPath());
        self::assertSame('organization.siret.required', $violations[0]->getMessageTemplate());
    }

    public function testSiretNotRequiredForForeignOrganization(): void
    {
        $organization = $this->createOrganization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->getHeadOfficeAddress()?->setCountry('BE');

        $violations = $this->validator->validate($organization);

        self::assertCount(0, $violations);
    }

    public function testSiretRequiredForRegisteredAssociation(): void
    {
        $organization = $this->createOrganization();
        $organization->setOrganizationType('ASSOCIATION');
        $organization->setAssociationRegistered(true);

        $violations = $this->validator->validate($organization);

        self::assertCount(1, $violations);
        self::assertSame('siret', $violations[0]->getPropertyPath());
        self::assertSame('organization.siret.required', $violations[0]->getMessageTemplate());
    }

    public function testLegalNatureMustMatchOrganizationType(): void
    {
        $organization = $this->createOrganization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setLegalNature('Commune');
        $organization->setSiret('10000000000016');

        $violations = $this->validator->validate($organization);

        self::assertCount(1, $violations);
        self::assertSame('legalNature', $violations[0]->getPropertyPath());
        self::assertSame('organization.legal_nature.invalid', $violations[0]->getMessageTemplate());
    }

    public function testLegalNatureAllowedForAssociation(): void
    {
        $organization = $this->createOrganization();
        $organization->setOrganizationType('ASSOCIATION');
        $organization->setLegalNature('Association loi 1901');

        $violations = $this->validator->validate($organization);

        self::assertCount(0, $violations);
    }

    private function createOrganization(): Organization
    {
        $organization = new Organization();
        $organization->setLegalName('Organisation test');
        $organization->setDisplayName('Organisation test');

        return $organization;
    }
}
