<?php

namespace App\EventSubscriber;

use App\Entity\AuthenticationLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Journalise les connexions réussies.
 */
class AuthenticationLogSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
            LoginFailureEvent::class => 'onLoginFailure',
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        if (!$user instanceof User) {
            return;
        }

        $request = $event->getRequest();

        $log = new AuthenticationLog();
        $log->setUser($user);
        $log->setIdentifier($user->getEmail());
        $log->setEventType(AuthenticationLog::EVENT_LOGIN_SUCCESS);
        $log->setIpAddress($request?->getClientIp());
        $log->setUserAgent($request?->headers->get('User-Agent'));

        $this->safePersist($log);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        $request = $event->getRequest();
        $identifier = mb_strtolower((string) $request->request->get('email', ''));

        $log = new AuthenticationLog();
        $log->setIdentifier('' !== $identifier ? $identifier : 'inconnu');
        $log->setEventType(AuthenticationLog::EVENT_LOGIN_FAILURE);
        $log->setIpAddress($request->getClientIp());
        $log->setUserAgent($request->headers->get('User-Agent'));
        $log->setFailureReason($this->extractFailureReason($event->getException()));

        $passport = $event->getPassport();
        if (null !== $passport) {
            $user = $passport->getUser();
            if ($user instanceof User) {
                $log->setUser($user);
                $log->setIdentifier($user->getEmail());
            }
        }

        $this->safePersist($log);
    }

    public function onLogout(LogoutEvent $event): void
    {
        $token = $event->getToken();
        $request = $event->getRequest();

        $identifier = 'inconnu';
        $log = new AuthenticationLog();

        if ($token && $token->getUser() instanceof User) {
            $user = $token->getUser();
            $log->setUser($user);
            $identifier = $user->getEmail();
        }

        $log->setIdentifier($identifier);
        $log->setEventType(AuthenticationLog::EVENT_LOGOUT);
        $log->setIpAddress($request->getClientIp());
        $log->setUserAgent($request->headers->get('User-Agent'));

        $this->safePersist($log);
    }

    private function extractFailureReason(AuthenticationException $exception): string
    {
        return $exception->getMessageKey();
    }

    private function safePersist(AuthenticationLog $log): void
    {
        try {
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            // Ne pas bloquer l’authentification si la journalisation échoue.
        }
    }
}
