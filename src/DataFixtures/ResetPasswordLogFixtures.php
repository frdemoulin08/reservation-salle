<?php

namespace App\DataFixtures;

use App\Entity\ResetPasswordLog;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ResetPasswordLogFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $events = [
            ResetPasswordLog::EVENT_REQUEST,
            ResetPasswordLog::EVENT_RESET_SUCCESS,
            ResetPasswordLog::EVENT_RESET_INVALID,
        ];

        $user = $manager->getRepository(User::class)->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);

        for ($i = 0; $i < 100; ++$i) {
            $event = $events[array_rand($events)];
            $log = new ResetPasswordLog();
            $log->setEventType($event);

            if (0 !== $i % 3 && $user instanceof User) {
                $log->setUser($user);
                $log->setIdentifier($user->getEmail() ?? 'admin@example.com');
            } else {
                $log->setIdentifier('unknown.user'.$i.'@example.com');
            }

            $log->setIpAddress('192.0.2.'.(($i % 200) + 1));
            $log->setUserAgent('Mozilla/5.0 (FixtureBot) ResetPassword');

            if (ResetPasswordLog::EVENT_RESET_INVALID === $event) {
                $log->setFailureReason('invalid_or_expired_token');
            }

            $log->setOccurredAt(new \DateTimeImmutable(sprintf('-%d minutes', $i * 7)));

            $manager->persist($log);
        }

        $manager->flush();
    }
}
