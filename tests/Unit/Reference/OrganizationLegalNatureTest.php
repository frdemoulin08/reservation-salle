<?php

namespace App\Tests\Unit\Reference;

use App\Reference\OrganizationLegalNature;
use PHPUnit\Framework\TestCase;

class OrganizationLegalNatureTest extends TestCase
{
    public function testAllowsEmptyValue(): void
    {
        self::assertTrue(OrganizationLegalNature::isAllowed(null, null));
        self::assertTrue(OrganizationLegalNature::isAllowed('ENTREPRISE', ''));
    }

    public function testRejectsWhenTypeMissing(): void
    {
        self::assertFalse(OrganizationLegalNature::isAllowed(null, 'SARL'));
    }

    public function testAllowsMatchingType(): void
    {
        self::assertTrue(OrganizationLegalNature::isAllowed('ASSOCIATION', 'Association loi 1901'));
        self::assertTrue(OrganizationLegalNature::isAllowed('ENTREPRISE', 'SARL'));
        self::assertTrue(OrganizationLegalNature::isAllowed('COLLECTIVITE', 'Commune'));
    }

    public function testRejectsMismatchedType(): void
    {
        self::assertFalse(OrganizationLegalNature::isAllowed('ENTREPRISE', 'Commune'));
        self::assertFalse(OrganizationLegalNature::isAllowed('COLLECTIVITE', 'SARL'));
    }
}
