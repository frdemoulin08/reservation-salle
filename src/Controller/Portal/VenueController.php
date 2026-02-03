<?php

namespace App\Controller\Portal;

use App\Entity\SiteDocumentType;
use App\Entity\VenueDocument;
use App\Repository\VenueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_USER")'))]
class VenueController extends AbstractController
{
    #[Route('/espace/sites', name: 'app_portal_venues_index')]
    public function index(VenueRepository $venueRepository): Response
    {
        $venues = $venueRepository->findAllWithPublicPhotos();

        return $this->render('portal/venues/index.html.twig', [
            'venues' => $venues,
        ]);
    }

    #[Route('/espace/sites/{publicIdentifier}', name: 'app_portal_venues_show', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function show(string $publicIdentifier, VenueRepository $venueRepository): Response
    {
        $venue = $venueRepository->findOneWithPublicPhotos($publicIdentifier);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        $photos = $venue->getDocuments()->filter(
            static fn (VenueDocument $document) => $document->isPublic() && SiteDocumentType::CODE_PHOTO === $document->getDocumentType()?->getCode()
        );

        return $this->render('portal/venues/show.html.twig', [
            'venue' => $venue,
            'photos' => $photos,
        ]);
    }
}
