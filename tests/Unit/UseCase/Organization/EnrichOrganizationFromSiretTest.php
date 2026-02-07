<?php

namespace App\Tests\Unit\UseCase\Organization;

use App\Entity\Organization;
use App\Service\CompanyLookupResult;
use App\Service\CompanyLookupService;
use App\UseCase\Organization\EnrichOrganizationFromSiret;
use PHPUnit\Framework\TestCase;

class EnrichOrganizationFromSiretTest extends TestCase
{
    public function testDoesNothingWhenSiretMissing(): void
    {
        $service = $this->createMock(CompanyLookupService::class);
        $service->expects(self::never())->method('isValidSiret');
        $service->expects(self::never())->method('findBySiret');

        $useCase = new EnrichOrganizationFromSiret($service);
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');

        $useCase->enrich($organization);

        self::assertNull($organization->getSiret());
    }

    public function testDoesNothingWhenSiretNotRequired(): void
    {
        $service = $this->createMock(CompanyLookupService::class);
        $service->expects(self::never())->method('isValidSiret');
        $service->expects(self::never())->method('findBySiret');

        $useCase = new EnrichOrganizationFromSiret($service);
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setSiret('10000000000016');
        $organization->getHeadOfficeAddress()?->setCountry('BE');

        $useCase->enrich($organization);
    }

    public function testEnrichesWhenSiretValidAndFieldsMissing(): void
    {
        $service = $this->createMock(CompanyLookupService::class);
        $service->expects(self::once())
            ->method('isValidSiret')
            ->with('10000000000016')
            ->willReturn(true);
        $service->expects(self::once())
            ->method('findBySiret')
            ->with('10000000000016')
            ->willReturn(CompanyLookupResult::ok([
                'siret' => '10000000000016',
                'legalName' => 'ACME SARL',
                'displayName' => 'ACME',
                'legalNature' => 'SARL',
                'organizationType' => 'ENTREPRISE',
                'address' => [
                    'line1' => '1 rue des Tests',
                    'postalCode' => '08000',
                    'city' => 'Charleville',
                    'country' => 'FR',
                ],
            ]));

        $useCase = new EnrichOrganizationFromSiret($service);
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setSiret('10000000000016');

        $useCase->enrich($organization);

        self::assertSame('ACME SARL', $organization->getLegalName());
        self::assertSame('ACME', $organization->getDisplayName());
        self::assertSame('SARL', $organization->getLegalNature());
        self::assertSame('ENTREPRISE', $organization->getOrganizationType());
        self::assertSame('1 rue des Tests', $organization->getHeadOfficeAddress()?->getLine1());
        self::assertSame('08000', $organization->getHeadOfficeAddress()?->getPostalCode());
        self::assertSame('Charleville', $organization->getHeadOfficeAddress()?->getCity());
        self::assertSame('FR', $organization->getHeadOfficeAddress()?->getCountry());
    }

    public function testSkipsEnrichmentWhenFieldsAlreadyFilled(): void
    {
        $service = $this->createMock(CompanyLookupService::class);
        $service->expects(self::never())->method('isValidSiret');
        $service->expects(self::never())->method('findBySiret');

        $useCase = new EnrichOrganizationFromSiret($service);
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setSiret('10000000000016');
        $organization->setLegalName('Déjà saisi');
        $organization->setDisplayName('Déjà saisi');
        $organization->setLegalNature('SARL');
        $organization->getHeadOfficeAddress()?->setLine1('1 rue');
        $organization->getHeadOfficeAddress()?->setPostalCode('08000');
        $organization->getHeadOfficeAddress()?->setCity('Charleville');
        $organization->getHeadOfficeAddress()?->setCountry('FR');

        $useCase->enrich($organization);
    }

    public function testSkipsWhenSiretInvalid(): void
    {
        $service = $this->createMock(CompanyLookupService::class);
        $service->expects(self::once())
            ->method('isValidSiret')
            ->with('10000000000015')
            ->willReturn(false);
        $service->expects(self::never())->method('findBySiret');

        $useCase = new EnrichOrganizationFromSiret($service);
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setSiret('10000000000015');

        $useCase->enrich($organization);

        self::assertSame('', $organization->getLegalName());
    }

    public function testSkipsWhenLookupUnavailable(): void
    {
        $service = $this->createMock(CompanyLookupService::class);
        $service->expects(self::once())
            ->method('isValidSiret')
            ->with('10000000000016')
            ->willReturn(true);
        $service->expects(self::once())
            ->method('findBySiret')
            ->with('10000000000016')
            ->willReturn(CompanyLookupResult::unavailable());

        $useCase = new EnrichOrganizationFromSiret($service);
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setSiret('10000000000016');

        $useCase->enrich($organization);

        self::assertSame('', $organization->getLegalName());
        self::assertSame('', $organization->getDisplayName());
    }

    public function testSkipsWhenLookupNotFound(): void
    {
        $service = $this->createMock(CompanyLookupService::class);
        $service->expects(self::once())
            ->method('isValidSiret')
            ->with('10000000000016')
            ->willReturn(true);
        $service->expects(self::once())
            ->method('findBySiret')
            ->with('10000000000016')
            ->willReturn(CompanyLookupResult::notFound());

        $useCase = new EnrichOrganizationFromSiret($service);
        $organization = new Organization();
        $organization->setOrganizationType('ENTREPRISE');
        $organization->setSiret('10000000000016');

        $useCase->enrich($organization);

        self::assertSame('', $organization->getLegalName());
    }
}
