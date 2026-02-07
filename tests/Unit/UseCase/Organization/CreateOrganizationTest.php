<?php

namespace App\Tests\Unit\UseCase\Organization;

use App\Entity\Organization;
use App\Service\CompanyLookupService;
use App\UseCase\Organization\CreateOrganization;
use App\UseCase\Organization\EnrichOrganizationFromSiret;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateOrganizationTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $organization = new Organization();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $companyLookupService = $this->createStub(CompanyLookupService::class);
        $enrichment = new EnrichOrganizationFromSiret($companyLookupService);
        $entityManager->expects(self::once())->method('persist')->with($organization);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateOrganization($entityManager, $enrichment);
        $useCase->execute($organization);
    }
}
