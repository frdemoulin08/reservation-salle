<?php

namespace App\Tests\Unit\UseCase\Organization;

use App\Entity\Organization;
use App\Service\CompanyLookupService;
use App\UseCase\Organization\EnrichOrganizationFromSiret;
use App\UseCase\Organization\UpdateOrganization;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateOrganizationTest extends TestCase
{
    public function testFlushes(): void
    {
        $organization = new Organization();
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $companyLookupService = $this->createStub(CompanyLookupService::class);
        $enrichment = new EnrichOrganizationFromSiret($companyLookupService);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateOrganization($entityManager, $enrichment);
        $useCase->execute($organization);
    }
}
