<?php

namespace App\Controller\Security;

use App\Entity\ResetPasswordLog;
use App\Entity\ResetPasswordRequest;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        #[Autowire('%app.mailer_from%')] private readonly string $mailerFrom,
    ) {
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password_request')]
    public function request(
        Request $request,
        ResetPasswordHelperInterface $resetPasswordHelper,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer,
        #[Autowire(service: 'limiter.password_reset_ip')] RateLimiterFactory $passwordResetLimiterIp,
        #[Autowire(service: 'limiter.password_reset_email')] RateLimiterFactory $passwordResetLimiterEmail,
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $clientKey = $request->getClientIp() ?? 'anonymous';
            $limit = $passwordResetLimiterIp->create($clientKey)->consume(1);
            if (!$limit->isAccepted()) {
                return $this->redirectToRoute('app_check_email');
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $email */
            $email = (string) $form->get('email')->getData();

            $emailKey = hash('sha256', mb_strtolower($email));
            $emailLimit = $passwordResetLimiterEmail->create($emailKey)->consume(1);
            if (!$emailLimit->isAccepted()) {
                return $this->redirectToRoute('app_check_email');
            }

            return $this->processSendingPasswordResetEmail($request, $email, $resetPasswordHelper, $entityManager, $mailer);
        }

        return $this->render('security/reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/mot-de-passe-oublie/envoye', name: 'app_check_email')]
    public function checkEmail(ResetPasswordHelperInterface $resetPasswordHelper): Response
    {
        $resetToken = $this->getTokenObjectFromSession();

        if (null === $resetToken) {
            $resetToken = $resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('security/reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    #[Route('/reinitialiser-mot-de-passe/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request,
        ResetPasswordHelperInterface $resetPasswordHelper,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        string $token,
    ): Response {
        try {
            /** @var User $user */
            $user = $resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface) {
            $this->logResetEvent($entityManager, ResetPasswordLog::EVENT_RESET_INVALID, null, '', $request, 'invalid_or_expired_token');
            $this->addFlash('danger', 'Votre lien de réinitialisation est invalide ou expiré.');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $resetPasswordHelper->removeResetRequest($token);
            $this->cleanSessionAfterReset();

            $encodedPassword = $passwordHasher->hashPassword($user, (string) $form->get('plainPassword')->getData());
            $user->setPassword($encodedPassword);

            $this->logResetEvent($entityManager, ResetPasswordLog::EVENT_RESET_SUCCESS, $user, $user->getEmail() ?? '', $request);
            $entityManager->flush();

            $currentToken = $tokenStorage->getToken();
            if ($currentToken && $currentToken->getUser() instanceof User) {
                $tokenUser = $currentToken->getUser();
                if ($tokenUser->getId() === $user->getId()) {
                    $tokenStorage->setToken(null);
                    $request->getSession()->invalidate();
                }
            }

            $this->addFlash('success', 'Votre mot de passe a été mis à jour.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
            'token' => $token,
        ]);
    }

    private function processSendingPasswordResetEmail(Request $request, string $email, ResetPasswordHelperInterface $resetPasswordHelper, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        /** @var User|null $user */
        $user = $entityManager->getRepository(User::class)->findOneBy([
            'email' => mb_strtolower($email),
        ]);

        $this->logResetEvent($entityManager, ResetPasswordLog::EVENT_REQUEST, $user, $email, $request);

        if (!$user || !$user->isActive()) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetRequestRepository = $entityManager->getRepository(ResetPasswordRequest::class);
            if (method_exists($resetRequestRepository, 'removeRequests')) {
                $resetRequestRepository->removeRequests($user);
            }

            $resetToken = $resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface) {
            return $this->redirectToRoute('app_check_email');
        }

        $emailMessage = (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->htmlTemplate('security/reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'user' => $user,
            ]);

        $mailer->send($emailMessage);

        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }

    private function logResetEvent(
        EntityManagerInterface $entityManager,
        string $eventType,
        ?User $user,
        string $identifier,
        Request $request,
        ?string $failureReason = null,
    ): void {
        $log = new ResetPasswordLog();
        $log->setEventType($eventType);
        $log->setUser($user);
        $log->setIdentifier($identifier);
        $log->setIpAddress($request->getClientIp());
        $log->setUserAgent($request->headers->get('User-Agent'));
        $log->setFailureReason($failureReason);

        $entityManager->persist($log);
        $entityManager->flush();
    }
}
