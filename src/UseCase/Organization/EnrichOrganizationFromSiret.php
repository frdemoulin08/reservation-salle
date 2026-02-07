<?php

namespace App\UseCase\Organization;

use App\Entity\Organization;
use App\Service\CompanyLookupResult;
use App\Service\CompanyLookupService;

final class EnrichOrganizationFromSiret
{
    public function __construct(private readonly CompanyLookupService $companyLookupService)
    {
    }

    public function enrich(Organization $organization): void
    {
        $siret = trim((string) ($organization->getSiret() ?? ''));
        if ('' === $siret) {
            return;
        }

        if (!$organization->requiresSiret()) {
            return;
        }

        if (!$this->shouldEnrich($organization)) {
            return;
        }

        if (!$this->companyLookupService->isValidSiret($siret)) {
            return;
        }

        $result = $this->companyLookupService->findBySiret($siret);
        if (CompanyLookupResult::STATUS_OK !== $result->status || !is_array($result->data)) {
            return;
        }

        $organization->applyLookupData($result->data);
    }

    private function shouldEnrich(Organization $organization): bool
    {
        $address = $organization->getHeadOfficeAddress();

        return '' === trim($organization->getLegalName())
            || '' === trim($organization->getDisplayName())
            || '' === trim((string) $organization->getOrganizationType())
            || '' === trim((string) $organization->getLegalNature())
            || null === $address
            || '' === trim((string) $address->getLine1())
            || '' === trim((string) $address->getPostalCode())
            || '' === trim((string) $address->getCity())
            || '' === trim((string) $address->getCountry());
    }
}
