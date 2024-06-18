<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Converter\StandardToFlat;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver;
use PcmtCoreBundle\ArrayConverter\StandardToFlat\Product\ConcatenatedAttributesConverter;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class ConcatenatedAttributesConverterTest extends TestCase
{
    /** @var AttributeColumnsResolver|Mock */
    protected $columnsResolverMock;

    /** @var mixed[] */
    private $supportedAttributeTypes = [];

    /** @var string */
    private $code = 'concatenated_test';

    protected function setUp(): void
    {
        $this->supportedAttributeTypes = [PcmtAtributeTypes::CONCATENATED_FIELDS];
        $this->columnsResolverMock = $this->createMock(AttributeColumnsResolver::class);
    }

    /** @dataProvider provideValidDataToConvert */
    public function testShouldReturnValidConvertedArray(array $input, array $converted): void
    {
        $attribute = new Attribute();
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $attribute->setCode($this->code);
        $concatenatedAttributesConverter = $this->getConcatenatedAttributesConverterInstance();

        $this->assertTrue($concatenatedAttributesConverter->supportsAttribute($attribute));
        $this->assertSame($converted, $concatenatedAttributesConverter->convert($attribute->getCode(), $input));
    }

    /**
     * @param array|string $input
     * @dataProvider provideInvalidDataToConvert
     */
    public function testShouldThrowExceptionIfInvalidDataFormat($input): void
    {
        $attribute = new Attribute();
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $attribute->setCode($this->code);
        $concatenatedAttributesConverter = $this->getConcatenatedAttributesConverterInstance();

        $this->expectException(\InvalidArgumentException::class);
        $concatenatedAttributesConverter->convert($attribute->getCode(), $input);
    }

    public function provideValidDataToConvert(): array
    {
        return [
            [
                [['data' => '100 EUR:0.250KG']],
                [$this->code => '100 EUR:0.250KG'],
            ],
            [
                [['data' => '200 PSI|100 kG/m2%%80 USD']],
                [$this->code => '200 PSI|100 kG/m2%%80 USD'],
            ],
        ];
    }

    public function provideInvalidDataToConvert(): array
    {
        return [
            [
                'data',
            ],
        ];
    }

    private function getConcatenatedAttributesConverterInstance(): ConcatenatedAttributesConverter
    {
        return new ConcatenatedAttributesConverter(
            $this->columnsResolverMock,
            $this->supportedAttributeTypes
        );
    }
}