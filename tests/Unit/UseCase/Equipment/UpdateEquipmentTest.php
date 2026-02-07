<?php

namespace App\Tests\Unit\UseCase\Equipment;

use App\Entity\Equipment;
use App\UseCase\Equipment\UpdateEquipment;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateEquipmentTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateEquipment($entityManager);

        $useCase->execute(new Equipment());
    }
}
