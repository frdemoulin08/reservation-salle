<?php

namespace App\Controller\Api;

use App\Service\CompanyLookupResult;
use App\Service\CompanyLookupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_APP_MANAGER")'))]
class CompanyLookupController extends AbstractController
{
    #[Route('/api/companies/siret/{siret}', name: 'app_api_company_siret', methods: ['GET'])]
    public function siret(string $siret, CompanyLookupService $companyLookupService): JsonResponse
    {
        $normalized = $companyLookupService->normalizeSiret($siret);
        if ('' === $normalized || !$companyLookupService->isValidSiret($normalized)) {
            return $this->json([
                'status' => 'invalid',
                'message' => 'Le SIRET doit contenir 14 chiffres valides.',
            ], 400);
        }

        $result = $companyLookupService->findBySiret($normalized);

        if ($result->status === CompanyLookupResult::STATUS_UNAVAILABLE) {
            return $this->json([
                'status' => 'unavailable',
                'message' => 'Le service d\'enrichissement est temporairement indisponible.',
            ], 503);
        }

        if ($result->status === CompanyLookupResult::STATUS_NOT_FOUND) {
            return $this->json([
                'status' => 'not_found',
                'found' => false,
            ]);
        }

        return $this->json([
            'status' => 'ok',
            'found' => true,
            'data' => $result->data,
        ]);
    }
}
