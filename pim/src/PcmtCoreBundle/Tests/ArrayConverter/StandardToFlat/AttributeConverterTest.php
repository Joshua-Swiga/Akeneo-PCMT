<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PcmtCoreBundle\ArrayConverter\StandardToFlat\AttributeConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeConverterTest extends TestCase
{
    /** @var ArrayConverterInterface|MockObject */
    private $baseAttributeConverterMock;

    /** @var ArrayConverterInterface */
    private $attributeArrayConverter;

    protected function setUp(): void
    {
        $this->baseAttributeConverterMock = $this->createMock(ArrayConverterInterface::class);
        $this->attributeArrayConverter = new AttributeConverter(
            $this->baseAttributeConverterMock
        );
    }

    /**
     * @dataProvider dataConvert
     * @params int|string|null
     */
    public function testConvert(array $item, array $baseAttributeConverterResult, array $expectedResult): void
    {
        $this->baseAttributeConverterMock
            ->expects($this->once())
            ->method('convert')
            ->with($baseAttributeConverterResult)
            ->willReturn($baseAttributeConverterResult);
        $result = $this->attributeArrayConverter->convert($item);
        $this->assertSame($expectedResult, $result);
    }

    public function dataConvert(): array
    {
        return [
            'merge results' => [
                [
                    'descriptions'     => [
                        'en_US' => 'en US',
                        'fr'    => 'FR',
                    ],
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
                [
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
                [
                    'description-en_US' => 'en US',
                    'description-fr'    => 'FR',
                    'code'              => 'some_code',
                    'first_attribute'   => 'first attribute',
                    'second_attribute'  => 'second attribute',
                ],
            ],
            'only baseAttribute\'s attributes' => [
                [
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
                [
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
                [
                    'code'              => 'some_code',
                    'first_attribute'   => 'first attribute',
                    'second_attribute'  => 'second attribute',
                ],
            ],
            'only attributeConverter\'s attributes' => [
                [
                    'concatenated'     => 'some:data',
                    'descriptions'     => [
                        'en_US' => 'en US',
                        'fr'    => 'FR',
                    ],
                ],
                [],
                [
                    'description-en_US' => 'en US',
                    'description-fr'    => 'FR',
                ],
            ],
        ];
    }
}