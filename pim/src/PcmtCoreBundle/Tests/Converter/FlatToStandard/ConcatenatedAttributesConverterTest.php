<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Converter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use PcmtCoreBundle\ArrayConverter\FlatToStandard\Product\ConcatenatedAttributesConverter;
use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

class ConcatenatedAttributesConverterTest extends TestCase
{
    /** @var FieldSplitter|Mock */
    protected $fieldSplitter;

    /** @var mixed[] */
    private $supportedAttributeTypes = [];

    /** @var string */
    private $code = 'concatenated_test';

    protected function setUp(): void
    {
        $this->supportedAttributeTypes = [PcmtAtributeTypes::CONCATENATED_FIELDS];
        $this->fieldSplitter = $this->createMock(FieldSplitter::class);
    }

    /** @dataProvider provideValidDataToConvert */
    public function testShouldReturnValidConvertedArray(string $input): void
    {
        $attribute = new Attribute();
        $attribute->setType(PcmtAtributeTypes::CONCATENATED_FIELDS);
        $attribute->setCode($this->code);
        $concatenatedAttributesConverter = $this->getConcatenatedAttributesConverterInstance();

        $attributeFieldInfo = [
            'attribute'   => $attribute,
            'locale_code' => null,
            'scope_code'  => null,
        ];

        $converted = $concatenatedAttributesConverter->convert($attributeFieldInfo, $input);
        $this->assertIsArray($converted);
        $this->assertIsArray($converted[$this->code]);
        $this->assertSame($input, $converted[$this->code][0]['data']);
    }

    public function provideValidDataToConvert(): array
    {
        return [
            ['100 EUR:0.250KG'],
            ['200 PSI|100 kG/m2%%80 USD'],
        ];
    }

    private function getConcatenatedAttributesConverterInstance(): ConcatenatedAttributesConverter
    {
        return new ConcatenatedAttributesConverter(
            $this->fieldSplitter,
            $this->supportedAttributeTypes
        );
    }
}