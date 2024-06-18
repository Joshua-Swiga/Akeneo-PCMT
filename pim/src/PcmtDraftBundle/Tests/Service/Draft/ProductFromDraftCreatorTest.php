<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\ProductDraftInterface;
use PcmtDraftBundle\Service\Draft\DraftValuesWithMissingAttributeFilter;
use PcmtDraftBundle\Service\Draft\ProductFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductFromDraftCreatorTest extends TestCase
{
    /** @var ProductBuilderInterface */
    private $productBuilderMock;

    /** @var ConverterInterface */
    private $productValueConverterMock;

    /** @var AttributeConverterInterface */
    private $localizedConverterMock;

    /** @var UserContext */
    private $userContextMock;

    /** @var FilterInterface */
    private $emptyValuesFilterMock;

    /** @var ObjectUpdaterInterface */
    private $productUpdaterMock;

    /** @var AttributeFilterInterface */
    private $productAttributeFilterMock;

    /** @var DraftValuesWithMissingAttributeFilter|MockObject */
    private $draftValuesWithMissingAttributesFilterMock;

    protected function setUp(): void
    {
        $this->productBuilderMock = $this->createMock(ProductBuilderInterface::class);
        $this->productValueConverterMock = $this->createMock(ConverterInterface::class);
        $this->productValueConverterMock->method('convert')->willReturn([]);
        $this->localizedConverterMock = $this->createMock(AttributeConverterInterface::class);
        $this->userContextMock = $this->createMock(UserContext::class);
        $this->userContextMock->method('getUiLocale')->willReturn($this->createMock(LocaleInterface::class));
        $this->emptyValuesFilterMock = $this->createMock(FilterInterface::class);
        $this->productUpdaterMock = $this->createMock(ObjectUpdaterInterface::class);
        $this->productAttributeFilterMock = $this->createMock(AttributeFilterInterface::class);
        $this->draftValuesWithMissingAttributesFilterMock = $this->createMock(DraftValuesWithMissingAttributeFilter::class);
    }

    /**
     * @dataProvider dataCreateNewObject
     */
    public function testCreateNewObject(ProductDraftInterface $draft): void
    {
        $productMock = $this->createMock(ProductInterface::class);
        $this->productBuilderMock->expects($this->once())->method('createProduct')->willReturn($productMock);

        $service = $this->getServiceInstance();

        $service->createNewObject($draft);
    }

    public function dataCreateNewObject(): array
    {
        $draft1 = $this->createMock(NewProductDraft::class);
        $draft2 = $this->createMock(NewProductDraft::class);
        $draft2->method('getProductData')->willReturn(['parent' => 1]);

        return [
            [$draft1],
            [$draft2],
        ];
    }

    /**
     * @dataProvider dataCreateForSaveForDraftForExistingObject
     */
    public function testCreateForSaveForDraftForExistingObject(ProductDraftInterface $draft, array $dataFiltered): void
    {
        $this->emptyValuesFilterMock->method('filter')->willReturn($dataFiltered);
        $this->productUpdaterMock->expects($this->once())->method('update');
        $this->localizedConverterMock->method('convertToDefaultFormats')->willReturn([]);
        $service = $this->getServiceInstance();
        $product = $service->createForSaveForDraftForExistingObject($draft);
        $this->assertInstanceOf(ProductInterface::class, $product);
    }

    public function dataCreateForSaveForDraftForExistingObject(): array
    {
        $productMock = $this->createMock(ProductInterface::class);
        $collection = new WriteValueCollection();
        $collection->add($this->createMock(ValueInterface::class));
        $productMock->method('getValuesForVariation')->willReturn($collection);
        $productMock->method('getId')->willReturn(22);
        $productMock->method('isVariant')->willReturn(true);
        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getProduct')->willReturn($productMock);
        $draft->method('getProductData')->willReturn([
            'values' => [
                'xxx' => 1,
            ],
        ]);

        return [
            [$draft, []],
            [$draft, ['xxx' => 'yyy']],
        ];
    }

    /**
     * @dataProvider dataCreateForSaveForDraftForExistingObjectNoProduct
     */
    public function testCreateForSaveForDraftForExistingObjectNoProduct(ProductDraftInterface $draft): void
    {
        $this->productUpdaterMock->expects($this->never())->method('update');

        $service = $this->getServiceInstance();

        $product = $service->createForSaveForDraftForExistingObject($draft);
        $this->assertNull($product);
    }

    public function dataCreateForSaveForDraftForExistingObjectNoProduct(): array
    {
        $draft = $this->createMock(ExistingProductDraft::class);
        $draft->method('getProduct')->willReturn(null);
        $draft->method('getProductData')->willReturn([
            'values' => [
                'xxx' => 2,
            ],
        ]);

        return [
            [$draft],
        ];
    }

    private function getServiceInstance(): ProductFromDraftCreator
    {
        return new ProductFromDraftCreator(
            $this->productBuilderMock,
            $this->productValueConverterMock,
            $this->localizedConverterMock,
            $this->userContextMock,
            $this->emptyValuesFilterMock,
            $this->productUpdaterMock,
            $this->productAttributeFilterMock,
            $this->draftValuesWithMissingAttributesFilterMock
        );
    }
}