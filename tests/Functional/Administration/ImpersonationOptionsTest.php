<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class ImpersonationOptionsTest extends DatabaseWebTestCase
{
    public function testOptionsAreSortedAndFormatted(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/impersonation/options');

        self::assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        self::assertIsString($content);

        $expectedOrder = [
            'DEMOULIN Frederic',
            'GARNIER Luc',
            'JACQUET Clément',
            'LEBLANC Marion',
            'MOREL Sophie',
        ];

        $positions = [];
        foreach ($expectedOrder as $label) {
            $pos = strpos($content, $label);
            self::assertNotFalse($pos, sprintf('Option label "%s" not found.', $label));
            $positions[] = $pos;
        }

        $sortedPositions = $positions;
        sort($sortedPositions);
        self::assertSame($sortedPositions, $positions, 'Option labels are not in the expected order.');
    }
}
