<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Akeneo\Tool\Component\Connector\Exception\StructureArrayConversionException;
use PcmtCoreBundle\ArrayConverter\FlatToStandard\AttributeConverter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeConverterTest extends TestCase
{
    /** @var FieldsRequirementChecker */
    private $fieldChecker;

    /** @var ArrayConverterInterface|MockObject */
    private $baseAttributeConverterMock;

    /** @var ArrayConverterInterface */
    private $attributeArrayConverter;

    protected function setUp(): void
    {
        $this->fieldChecker = new FieldsRequirementChecker();
        $this->baseAttributeConverterMock = $this->createMock(ArrayConverterInterface::class);
        $this->attributeArrayConverter = new AttributeConverter(
            $this->fieldChecker,
            $this->baseAttributeConverterMock
        );
    }

    /**
     * @dataProvider dataConvertFieldCheckerThrowException
     */
    public function testConvertFieldCheckerThrowValidationException(array $item, string $exception): void
    {
        $this->expectException($exception);
        $this->attributeArrayConverter->convert($item);
    }

    public function dataConvertFieldCheckerThrowException(): array
    {
        return [
            'item is empty'             => [
                [],
                StructureArrayConversionException::class,
            ],
            'attribute code is missing' => [
                [
                    'attribute' => '',
                ],
                StructureArrayConversionException::class,
            ],
            'attribute code is empty'   => [
                [
                    'code' => '',
                ],
                DataArrayConversionException::class,
            ],
        ];
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
                    'code'              => 'some_code',
                    'concatenated'      => '',
                    'description-en_US' => 'en US',
                    'description-fr'    => 'FR',
                    'first_attribute'   => 'first attribute',
                    'second_attribute'  => 'second attribute',
                ],
                [
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
                [
                    'descriptions'     => [
                        'en_US' => 'en US',
                        'fr'    => 'FR',
                    ],
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
            ],
            'only baseAttribute\'s attributes' => [
                [
                    'code'              => 'some_code',
                    'first_attribute'   => 'first attribute',
                    'second_attribute'  => 'second attribute',
                ],
                [
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
                [
                    'descriptions'     => [],
                    'code'             => 'some_code',
                    'first_attribute'  => 'first attribute',
                    'second_attribute' => 'second attribute',
                ],
            ],
            'only attributeConverter\'s attributes' => [
                [
                    'code'              => 'some_code',
                    'concatenated'      => '',
                    'description-en_US' => 'en US',
                    'description-fr'    => 'FR',
                ],
                [
                    'code'              => 'some_code',
                ],
                [
                    'descriptions'     => [
                        'en_US' => 'en US',
                        'fr'    => 'FR',
                    ],
                    'code'             => 'some_code',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataConvertFields
     *
     * @param int|string|null $data
     */
    public function testConvertFields(string $field, $data, array $expectedResult): void
    {
        $result = $this->attributeArrayConverter->convertFields($field, $data, []);
        $this->assertSame($expectedResult, $result);
    }

    public function dataConvertFields(): array
    {
        return [
            'base language description'               => [
                'description-en_US',
                'custom description',
                [
                    'descriptions' => [
                        'en_US' => 'custom description',
                    ],
                ],
            ],
            'random language description'             => [
                'description-random_language',
                'another description',
                [
                    'descriptions' => [
                        'random_language' => 'another description',
                    ],
                ],
            ],
            'null description is not parse to string' => [
                'description-en_US',
                null,
                [
                    'descriptions' => [
                        'en_US' => null,
                    ],
                ],
            ],
            'concatenated attribute is not converted' => [
                'concatenated',
                'concatenated:attribute',
                [],
            ],
            'no supported field'                      => [
                'no_supported_field',
                'no supported field daa',
                [],
            ],
        ];
    }

    /**
     * @dataProvider dataSupportField
     */
    public function testSupportField(string $field, bool $expectedResult): void
    {
        $result = $this->attributeArrayConverter->supportField($field);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportField(): array
    {
        return [
            [
                'concatenated',
                true,
            ],
            [
                'description-en_US',
                true,
            ],
            [
                'description-fr',
                true,
            ],
            [
                'not_supported_field',
                false,
            ],
        ];
    }
}