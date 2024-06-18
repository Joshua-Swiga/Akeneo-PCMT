<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Extension\ConcatenatedAttribute\Structure\Component\Comparator;

use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\Comparator\ConcatenatedComparator;
use PHPUnit\Framework\TestCase;

class ConcatenatedComparatorTest extends TestCase
{
    /** @var ConcatenatedComparator */
    private $comparator;

    protected function setUp(): void
    {
        $this->comparator = new ConcatenatedComparator(['pcmt_catalog_concatenated']);
    }

    public function dataCompare(): array
    {
        return [
            [
                'data'     => [
                    'locale' => 'en_US',
                    'scope'  => 'GS1_GDSN',
                    'data'   => '',
                ],
                'original' => [
                    'locale' => 'en_US',
                    'scope'  => 'GS1_GDSN',
                    'data'   => 'PRODUCT_DESCRIPTION  [missing]---STRENGTH  [missing]',
                ],
                'result'   => null,
            ],
            [
                'data'     => [
                    'locale' => 'en_US',
                    'scope'  => 'GS1_GDSN',
                    'data'   => '',
                ],
                'original' => [],
                'result'   => null,
            ],
        ];
    }

    /**
     * @dataProvider dataCompare
     */
    public function testCompare(array $data, array $original, ?array $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->comparator->compare($data, $original));
    }

    public function dataSupports(): array
    {
        return [
            [
                'type'   => 'pcmt_catalog_concatenated',
                'result' => true,
            ],
            [
                'type'   => 'pim_catalog_boolean',
                'result' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataSupports
     */
    public function testSupports(string $type, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->comparator->supports($type));
    }
}