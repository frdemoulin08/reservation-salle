<?php

namespace App\Tests\Unit\UseCase\User;

use App\Entity\User;
use App\UseCase\User\CreateUser;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserTest extends TestCase
{
    public function testPersistsAndHashesPassword(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(User::class));
        $entityManager->expects(self::once())->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects(self::once())
            ->method('hashPassword')
            ->with(self::isInstanceOf(User::class), 'secret')
            ->willReturn('hashed');

        $useCase = new CreateUser($entityManager, $passwordHasher);

        $user = new User();
        $useCase->execute($user, 'secret');

        self::assertSame('hashed', $user->getPassword());
    }

    public function testPersistsWithoutPassword(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(User::class));
        $entityManager->expects(self::once())->method('flush');

        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->expects(self::never())->method('hashPassword');

        $useCase = new CreateUser($entityManager, $passwordHasher);

        $user = new User();
        $useCase->execute($user, '');

        self::assertNull($user->getPassword());
    }
}
