<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\Converter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use PcmtCoreBundle\ArrayConverter\FlatToStandard\Product\MetricConverter;
use PcmtCoreBundle\Entity\Attribute;
use PHPUnit\Framework\TestCase;

class MetricConverterTest extends TestCase
{
    /** @var FieldSplitter */
    protected $fieldSplitter;

    /** @var mixed[] */
    private $supportedAttributeTypes = [];

    /** @var string */
    private $code = 'attribute_code_test';

    protected function setUp(): void
    {
        $this->supportedAttributeTypes = ['pim_catalog_metric'];
        $this->fieldSplitter = new FieldSplitter();
    }

    /** @dataProvider provideValidDataToConvert */
    public function testShouldReturnValidConvertedArray(string $input): void
    {
        $attribute = new Attribute();
        $attribute->setType('pim_catalog_metric');
        $attribute->setCode($this->code);
        $converter = $this->getConverterInstance();

        $attributeFieldInfo = [
            'attribute'   => $attribute,
            'locale_code' => null,
            'scope_code'  => null,
        ];

        $converted = $converter->convert($attributeFieldInfo, $input);
        $this->assertIsArray($converted);
        $this->assertIsArray($converted[$this->code]);
        $this->assertIsArray($converted[$this->code][0]['data']);
        $data = $converted[$this->code][0]['data'];
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('unit', $data);
        $recreatedValue = $data['amount'] . ' ' . $data['unit'];
        $this->assertSame($input, $recreatedValue);
    }

    public function provideValidDataToConvert(): array
    {
        return [
            ['10.00 GRAMS'],
            ['20.00 GRAMS PER UNIT'],
        ];
    }

    private function getConverterInstance(): MetricConverter
    {
        return new MetricConverter(
            $this->fieldSplitter,
            $this->supportedAttributeTypes
        );
    }
}