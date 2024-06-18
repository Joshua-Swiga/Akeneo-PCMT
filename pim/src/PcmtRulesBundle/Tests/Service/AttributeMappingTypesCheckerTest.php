<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service;

use PcmtRulesBundle\Service\AttributeMappingTypesChecker;
use PHPUnit\Framework\TestCase;

class AttributeMappingTypesCheckerTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function dataIfPossible(): array
    {
        return [
            [
                'type1', 'type1', true,
            ],
            [
                'type1', 'type2', false,
            ],
            [
                'pim_catalog_text', 'type2', false,
            ],
            [
                'pim_catalog_text', 'pim_catalog_simpleselect', true,
            ],
            [
                'pim_catalog_simpleselect', 'pim_catalog_text', true,
            ],
        ];
    }

    /**
     * @dataProvider dataIfPossible
     */
    public function testCheckIfPossible(string $typeSource, string $typeDestination, bool $result): void
    {
        $checker = $this->getAttributeMappingTypesCheckerInstance();
        $this->assertEquals($result, $checker->checkIfPossible($typeSource, $typeDestination));
    }

    private function getAttributeMappingTypesCheckerInstance(): AttributeMappingTypesChecker
    {
        return new AttributeMappingTypesChecker();
    }
}