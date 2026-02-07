<?php

namespace App\Tests\Unit\UseCase\User;

use App\Entity\User;
use App\UseCase\User\UpdateUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UpdateUserTest extends TestCase
{
    public function testFlushesAndHashesPassword(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with(self::isInstanceOf(User::class), 'secret')
            ->willReturn('hashed');

        $useCase = new UpdateUser($entityManager, $passwordHasher);

        $user = new User();
        $useCase->execute($user, 'secret');

        self::assertSame('hashed', $user->getPassword());
    }

    public function testFlushesWithoutPassword(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects(self::never())->method('hashPassword');

        $useCase = new UpdateUser($entityManager, $passwordHasher);

        $user = new User();
        $user->setPassword('existing');

        $useCase->execute($user, '');

        self::assertSame('existing', $user->getPassword());
    }
}
