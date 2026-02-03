<?php

namespace App\Controller\Administration;

use App\Service\BanAddressClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/administration/adresses', name: 'app_admin_addresses_')]
class AddressLookupController extends AbstractController
{
    #[Route('/ban', name: 'ban', methods: ['GET'])]
    public function ban(Request $request, BanAddressClient $client): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));
        $limit = (int) $request->query->get('limit', 5);

        if (mb_strlen($query) < 3) {
            return $this->json(['results' => []]);
        }

        return $this->json([
            'results' => $client->search($query, max(1, min(10, $limit))),
        ]);
    }
}
