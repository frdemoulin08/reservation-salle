<?php

namespace App\Tests\Unit\UseCase\SiteDocumentType;

use App\Entity\SiteDocumentType;
use App\UseCase\SiteDocumentType\UpdateSiteDocumentType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateSiteDocumentTypeTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateSiteDocumentType($entityManager);

        $useCase->execute(new SiteDocumentType());
    }
}
