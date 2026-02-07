<?php

namespace App\Tests\Unit\UseCase\SiteDocumentType;

use App\Entity\SiteDocumentType;
use App\UseCase\SiteDocumentType\CreateSiteDocumentType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateSiteDocumentTypeTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(SiteDocumentType::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateSiteDocumentType($entityManager);

        $useCase->execute(new SiteDocumentType());
    }
}
