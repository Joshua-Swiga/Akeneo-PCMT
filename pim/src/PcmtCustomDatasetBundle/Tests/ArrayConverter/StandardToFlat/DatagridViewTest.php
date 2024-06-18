<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\ArrayConverter\StandardToFlat;

use PHPUnit\Framework\TestCase;

class DatagridViewTest extends TestCase
{
    /**
     * @dataProvider dataConvertProperty
     *
     * @param int|string|array|null $data
     */
    public function testConvertProperty(string $property, $data, array $expectedResult): void
    {
        $datagridViewArrayConverter = new FakeDatagridView();
        $result = $datagridViewArrayConverter->convertProperty($property, $data, [], []);
        $this->assertSame($expectedResult, $result);
    }

    public function dataConvertProperty(): array
    {
        return [
            'label translations' => [
                'property'       => 'labels',
                'data'           => [
                    'en_US' => 'Hello World',
                    'pl'    => 'Witaj świecie',
                ],
                'expectedResult' => [
                    'label-en_US' => 'Hello World',
                    'label-pl'    => 'Witaj świecie',
                ],
            ],
            'Some string values' => [
                'property'       => 'property_1',
                'data'           => 'property',
                'expectedResult' => [
                    'property_1' => 'property',
                ],
            ],
            'Some int values' => [
                'property'       => 'property_1',
                'data'           => 1,
                'expectedResult' => [
                    'property_1' => '1',
                ],
            ],
            'Some null values' => [
                'property'       => 'property_1',
                'data'           => null,
                'expectedResult' => [
                    'property_1' => '',
                ],
            ],
        ];
    }
}