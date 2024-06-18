<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Helper;

use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamilyVariant\EntityWithFamilyVariantAttributesProvider;
use PcmtDraftBundle\Service\Helper\UnexpectedAttributesFilter;
use PcmtDraftBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PHPUnit\Framework\TestCase;

class UnexpectedAttributesFilterTest extends TestCase
{
    /** @var UnexpectedAttributesFilter */
    private $unexpectedAttributesFilter;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributeProviderMock;

    protected function setUp(): void
    {
        $this->attributeProviderMock = $this->createMock(EntityWithFamilyVariantAttributesProvider::class);

        $this->unexpectedAttributesFilter = new UnexpectedAttributesFilter(
            $this->attributeProviderMock
        );
    }

    public function dataFilter(): array
    {
        return [
            [
                'values'          => [
                    'price_reference-USD' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 1.89,
                        ],
                    ],
                    'FREQUENCY'           => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Daily',
                        ],
                    ],
                ],
                'expected_values' => [
                    'FREQUENCY' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => 'Daily',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataFilter
     */
    public function testFilterWhenPassingProductModel(array $values, array $expectedValues): void
    {
        $productModel = (new ProductModelBuilder())
            ->build();

        $attribute = (new AttributeBuilder())
            ->withCode('FREQUENCY')
            ->build();

        $this->attributeProviderMock
            ->method('getAttributes')
            ->willReturn([$attribute]);

        $filteredValues = $this->unexpectedAttributesFilter->filter($productModel, $values);

        $this->assertEquals($expectedValues, $filteredValues);
    }

    /**
     * @dataProvider dataFilter
     */
    public function testFilterWhenPassingProduct(array $values, array $expectedValues): void
    {
        $productModel = (new ProductModelBuilder())
            ->build();

        $attribute = (new AttributeBuilder())
            ->withCode('FREQUENCY')
            ->build();

        $this->attributeProviderMock
            ->method('getAttributes')
            ->willReturn([$attribute]);

        $filteredValues = $this->unexpectedAttributesFilter->filter($productModel, $values);

        $this->assertEquals($expectedValues, $filteredValues);
    }
}