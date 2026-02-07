<?php

namespace App\Tests\Unit\UseCase\ResetPassword;

use App\Entity\ResetPasswordLog;
use App\Entity\User;
use App\UseCase\ResetPassword\LogResetPasswordEvent;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class LogResetPasswordEventTest extends TestCase
{
    public function testPersistsLog(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(ResetPasswordLog::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new LogResetPasswordEvent($entityManager);

        $useCase->execute(
            ResetPasswordLog::EVENT_REQUEST,
            new User(),
            'user@example.test',
            '127.0.0.1',
            'Agent',
            null,
        );
    }
}
