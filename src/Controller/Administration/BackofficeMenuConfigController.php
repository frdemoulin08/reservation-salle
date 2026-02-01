<?php

namespace App\Controller\Administration;

use App\Entity\BackofficeMenuConfig;
use App\Form\BackofficeMenuConfigType;
use App\Repository\BackofficeMenuConfigRepository;
use App\Service\BackofficeMenuConfigProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/menu-backoffice', name: 'app_admin_menu_config')]
#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
class BackofficeMenuConfigController extends AbstractController
{
    public function __invoke(
        Request $request,
        BackofficeMenuConfigRepository $repository,
        BackofficeMenuConfigProvider $provider,
        EntityManagerInterface $entityManager,
    ): Response {
        $configEntity = $repository->findActive() ?? (new BackofficeMenuConfig())
            ->setConfig($provider->getConfig())
            ->setIsActive(true);

        $rawConfig = json_encode($configEntity->getConfig(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $form = $this->createForm(BackofficeMenuConfigType::class, $configEntity);
        $form->get('config')->setData($rawConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $raw = (string) $form->get('config')->getData();
            $decoded = json_decode($raw, true);

            if (!is_array($decoded)) {
                $this->addFlash('error', 'Le JSON est invalide.');
            } else {
                $configEntity->setConfig($decoded);
                $configEntity->setIsActive((bool) $form->get('isActive')->getData());

                $entityManager->persist($configEntity);
                $entityManager->flush();

                $this->addFlash('success', 'Configuration du menu enregistrée.');

                return $this->redirectToRoute('app_admin_menu_config');
            }
        }

        return $this->render('admin/menu_config/edit.html.twig', [
            'form' => $form->createView(),
            'active_menu' => 'menu_config',
        ]);
    }
}
