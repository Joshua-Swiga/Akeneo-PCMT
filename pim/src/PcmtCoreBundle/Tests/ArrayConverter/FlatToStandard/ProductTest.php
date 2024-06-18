<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\Product as AkeneoProduct;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtCoreBundle\ArrayConverter\FlatToStandard\Product;
use PcmtCoreBundle\Tests\TestDataBuilder\AttributeBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    /** @var Product */
    private $productConverter;

    /** @var AkeneoProduct|MockObject */
    private $pimProductConverterMock;

    /** @var AttributeRepositoryInterface|MockObject */
    private $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->pimProductConverterMock = $this->createMock(AkeneoProduct::class);
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);

        $this->productConverter = new Product(
            $this->pimProductConverterMock,
            $this->attributeRepositoryMock
        );
    }

    public function dataItemWithConcatenatedAttribute(): array
    {
        return [
            [
                [
                    'sku'             => '30860002311808',
                    'categories'      => '10000675,GDSN,GS1',
                    'enabled'         => '1',
                    'family'          => 'GS1_GDSN',
                    'groups'          => '',
                    'Buggy_Attribute' => 'PRODUCT_DESCRIPTION  [missing]|ACTIVE_INGREDIENT  [missing]',
                ],
                [
                    'sku'        => '30860002311808',
                    'categories' => '10000675,GDSN,GS1',
                    'enabled'    => '1',
                    'family'     => 'GS1_GDSN',
                    'groups'     => '',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataItemWithConcatenatedAttribute
     */
    public function testConvertIfThereIsAConcatenatedAttribute(
        array $item,
        array $expectedItem
    ): void {
        $this->attributeRepositoryMock
            ->method('findBy')
            ->willReturn([(new AttributeBuilder())->withCode('Buggy_Attribute')->build()]);

        $this->pimProductConverterMock
            ->expects($this->once())
            ->method('convert')
            ->with($expectedItem);

        $this->productConverter->convert($item);
    }
}