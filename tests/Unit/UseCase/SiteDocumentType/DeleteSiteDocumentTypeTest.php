<?php

namespace App\Tests\Unit\UseCase\SiteDocumentType;

use App\Entity\SiteDocumentType;
use App\Entity\VenueDocument;
use App\UseCase\SiteDocumentType\DeleteSiteDocumentType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteSiteDocumentTypeTest extends TestCase
{
    public function testRejectsWhenUsed(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $documentType = new SiteDocumentType();
        $documentType->getDocuments()->add(new VenueDocument());

        $useCase = new DeleteSiteDocumentType($entityManager);

        self::assertFalse($useCase->execute($documentType));
    }

    public function testDeletesWhenUnused(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(SiteDocumentType::class));
        $entityManager->expects(self::once())->method('flush');

        $documentType = new SiteDocumentType();

        $useCase = new DeleteSiteDocumentType($entityManager);

        self::assertTrue($useCase->execute($documentType));
    }
}
