<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Embeddable\Address;
use App\Entity\Organization;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OrganizationTest extends TestCase
{
    #[DataProvider('requiresSiretProvider')]
    public function testRequiresSiret(?string $country, ?string $type, bool $registeredAssociation, bool $expected): void
    {
        $organization = new Organization();
        $organization->setOrganizationType($type);
        $organization->setAssociationRegistered($registeredAssociation);

        $address = $organization->getHeadOfficeAddress() ?? new Address();
        $address->setCountry($country);
        $organization->setHeadOfficeAddress($address);

        self::assertSame($expected, $organization->requiresSiret());
    }

    public function testIsHeadOfficeInFranceIsCaseInsensitive(): void
    {
        $organization = new Organization();
        $organization->getHeadOfficeAddress()?->setCountry('fr');

        self::assertTrue($organization->isHeadOfficeInFrance());

        $organization->getHeadOfficeAddress()?->setCountry('be');

        self::assertFalse($organization->isHeadOfficeInFrance());
    }

    public function testApplyLookupDataFillsMissingValues(): void
    {
        $organization = new Organization();
        $organization->setHeadOfficeAddress(null);

        $organization->applyLookupData([
            'siret' => '12345678901234',
            'legalName' => 'ACME SARL',
            'displayName' => 'ACME',
            'legalNature' => 'SARL',
            'organizationType' => 'ENTREPRISE',
            'address' => [
                'line1' => '1 rue de la Paix',
                'postalCode' => '75000',
                'city' => 'Paris',
                'country' => 'FR',
            ],
        ]);

        self::assertSame('12345678901234', $organization->getSiret());
        self::assertSame('ACME SARL', $organization->getLegalName());
        self::assertSame('ACME', $organization->getDisplayName());
        self::assertSame('SARL', $organization->getLegalNature());
        self::assertSame('ENTREPRISE', $organization->getOrganizationType());
        self::assertSame('1 rue de la Paix', $organization->getHeadOfficeAddress()?->getLine1());
        self::assertSame('75000', $organization->getHeadOfficeAddress()?->getPostalCode());
        self::assertSame('Paris', $organization->getHeadOfficeAddress()?->getCity());
        self::assertSame('FR', $organization->getHeadOfficeAddress()?->getCountry());
    }

    public function testApplyLookupDataDoesNotOverwriteByDefault(): void
    {
        $organization = new Organization();
        $organization->setSiret('11111111111111');
        $organization->setLegalName('Existing Legal');
        $organization->setDisplayName('Existing Display');
        $organization->setLegalNature('SARL');
        $organization->setOrganizationType('ENTREPRISE');
        $organization->getHeadOfficeAddress()?->setLine1('Old line');
        $organization->getHeadOfficeAddress()?->setPostalCode('75000');
        $organization->getHeadOfficeAddress()?->setCity('Paris');
        $organization->getHeadOfficeAddress()?->setCountry('FR');

        $organization->applyLookupData([
            'siret' => '22222222222222',
            'legalName' => 'New Legal',
            'displayName' => 'New Display',
            'legalNature' => 'SAS',
            'organizationType' => 'AUTRE',
            'address' => [
                'line1' => 'New line',
                'postalCode' => '69000',
                'city' => 'Lyon',
                'country' => 'FR',
            ],
        ]);

        self::assertSame('11111111111111', $organization->getSiret());
        self::assertSame('Existing Legal', $organization->getLegalName());
        self::assertSame('Existing Display', $organization->getDisplayName());
        self::assertSame('SARL', $organization->getLegalNature());
        self::assertSame('ENTREPRISE', $organization->getOrganizationType());
        self::assertSame('Old line', $organization->getHeadOfficeAddress()?->getLine1());
        self::assertSame('75000', $organization->getHeadOfficeAddress()?->getPostalCode());
        self::assertSame('Paris', $organization->getHeadOfficeAddress()?->getCity());
    }

    public function testApplyLookupDataOverwritesWhenRequested(): void
    {
        $organization = new Organization();
        $organization->setSiret('11111111111111');
        $organization->setLegalName('Existing Legal');
        $organization->setDisplayName('Existing Display');
        $organization->setLegalNature('SARL');
        $organization->setOrganizationType('ENTREPRISE');
        $organization->getHeadOfficeAddress()?->setLine1('Old line');
        $organization->getHeadOfficeAddress()?->setPostalCode('75000');
        $organization->getHeadOfficeAddress()?->setCity('Paris');
        $organization->getHeadOfficeAddress()?->setCountry('FR');

        $organization->applyLookupData([
            'siret' => '22222222222222',
            'legalName' => 'New Legal',
            'displayName' => 'New Display',
            'legalNature' => 'SAS',
            'organizationType' => 'AUTRE',
            'address' => [
                'line1' => 'New line',
                'postalCode' => '69000',
                'city' => 'Lyon',
                'country' => 'BE',
            ],
        ], true);

        self::assertSame('22222222222222', $organization->getSiret());
        self::assertSame('New Legal', $organization->getLegalName());
        self::assertSame('New Display', $organization->getDisplayName());
        self::assertSame('SAS', $organization->getLegalNature());
        self::assertSame('AUTRE', $organization->getOrganizationType());
        self::assertSame('New line', $organization->getHeadOfficeAddress()?->getLine1());
        self::assertSame('69000', $organization->getHeadOfficeAddress()?->getPostalCode());
        self::assertSame('Lyon', $organization->getHeadOfficeAddress()?->getCity());
        self::assertSame('BE', $organization->getHeadOfficeAddress()?->getCountry());
    }

    public function testRequiresSiretReturnsFalseWhenNoHeadOfficeAddress(): void
    {
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setHeadOfficeAddress(null);

        self::assertFalse($organization->requiresSiret());
    }

    public static function requiresSiretProvider(): array
    {
        return [
            ['FR', 'ENTREPRISE', false, true],
            ['FR', 'COLLECTIVITE', false, true],
            ['FR', 'ASSOCIATION', true, true],
            ['FR', 'ASSOCIATION', false, false],
            ['BE', 'ENTREPRISE', false, false],
            [null, 'ENTREPRISE', false, false],
        ];
    }
}
