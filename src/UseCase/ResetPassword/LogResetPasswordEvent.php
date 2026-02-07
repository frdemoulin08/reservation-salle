<?php

namespace App\UseCase\ResetPassword;

use App\Entity\ResetPasswordLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final class LogResetPasswordEvent
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(
        string $eventType,
        ?User $user,
        string $identifier,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $failureReason = null,
    ): void {
        $log = new ResetPasswordLog();
        $log->setEventType($eventType);
        $log->setUser($user);
        $log->setIdentifier($identifier);
        $log->setIpAddress($ipAddress);
        $log->setUserAgent($userAgent);
        $log->setFailureReason($failureReason);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
