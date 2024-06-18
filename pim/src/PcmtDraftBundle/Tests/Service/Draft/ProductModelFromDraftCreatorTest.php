<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\AttributeFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Service\Draft\DraftValuesWithMissingAttributeFilter;
use PcmtDraftBundle\Service\Draft\ProductModelFromDraftCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductModelFromDraftCreatorTest extends TestCase
{
    /** @var SimpleFactoryInterface */
    private $productModelFactory;

    /** @var ConverterInterface|MockObject */
    private $productValueConverterMock;

    /** @var AttributeConverterInterface|MockObject */
    private $localizedConverterMock;

    /** @var UserContext|MockObject */
    private $userContextMock;

    /** @var FilterInterface|MockObject */
    private $emptyValuesFilterMock;

    /** @var ObjectUpdaterInterface|MockObject */
    private $productUpdaterMock;

    /** @var AttributeFilterInterface|MockObject */
    private $productAttributeFilterMock;

    /** @var DraftValuesWithMissingAttributeFilter|MockObject */
    private $draftValuesWithMissingAttributeFilterMock;

    protected function setUp(): void
    {
        $this->productModelFactory = $this->createMock(SimpleFactoryInterface::class);
        $this->productValueConverterMock = $this->createMock(ConverterInterface::class);
        $this->productValueConverterMock->method('convert')->willReturn([]);
        $this->localizedConverterMock = $this->createMock(AttributeConverterInterface::class);
        $this->userContextMock = $this->createMock(UserContext::class);
        $this->userContextMock->method('getUiLocale')->willReturn($this->createMock(LocaleInterface::class));
        $this->emptyValuesFilterMock = $this->createMock(FilterInterface::class);
        $this->productUpdaterMock = $this->createMock(ObjectUpdaterInterface::class);
        $this->productAttributeFilterMock = $this->createMock(AttributeFilterInterface::class);
        $this->draftValuesWithMissingAttributeFilterMock = $this->createMock(DraftValuesWithMissingAttributeFilter::class);
    }

    /**
     * @dataProvider dataCreateNewObject
     */
    public function testCreateNewObject(ProductModelDraftInterface $draft): void
    {
        $productMock = $this->createMock(ProductModelInterface::class);
        $this->productModelFactory->expects($this->once())->method('create')->willReturn($productMock);

        $service = $this->getServiceInstance();

        $service->createNewObject($draft);
    }

    public function dataCreateNewObject(): array
    {
        $draft1 = $this->createMock(NewProductModelDraft::class);
        $draft1->method('getProductData')->willReturn([]);
        $draft2 = $this->createMock(NewProductModelDraft::class);
        $draft2->method('getProductData')->willReturn(['parent' => 1]);

        return [
            [$draft1],
            [$draft2],
        ];
    }

    /**
     * @dataProvider dataCreateForSaveForDraftForExistingObject
     */
    public function testCreateForSaveForDraftForExistingObject(ProductModelDraftInterface $draft, array $dataFiltered): void
    {
        $this->emptyValuesFilterMock->method('filter')->willReturn($dataFiltered);
        $this->productUpdaterMock->expects($this->once())->method('update');
        $this->localizedConverterMock->method('convertToDefaultFormats')->willReturn([]);
        $service = $this->getServiceInstance();
        $product = $service->createForSaveForDraftForExistingObject($draft);
        $this->assertInstanceOf(ProductModelInterface::class, $product);
    }

    public function dataCreateForSaveForDraftForExistingObject(): array
    {
        $productMock = $this->createMock(ProductModelInterface::class);
        $collection = new WriteValueCollection();
        $collection->add($this->createMock(ValueInterface::class));
        $productMock->method('getValuesForVariation')->willReturn($collection);
        $productMock->method('getId')->willReturn(22);
        $productMock->method('isRoot')->willReturn(false);
        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn($productMock);
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
    public function testCreateForSaveForDraftForExistingObjectNoProduct(ProductModelDraftInterface $draft): void
    {
        $this->productUpdaterMock->expects($this->never())->method('update');

        $service = $this->getServiceInstance();

        $product = $service->createForSaveForDraftForExistingObject($draft);
        $this->assertNull($product);
    }

    public function dataCreateForSaveForDraftForExistingObjectNoProduct(): array
    {
        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getProductModel')->willReturn(null);
        $draft->method('getProductData')->willReturn([
            'values' => [
                'xxx' => 2,
            ],
        ]);

        return [
            [$draft],
        ];
    }

    private function getServiceInstance(): ProductModelFromDraftCreator
    {
        return new ProductModelFromDraftCreator(
            $this->productModelFactory,
            $this->productValueConverterMock,
            $this->localizedConverterMock,
            $this->userContextMock,
            $this->emptyValuesFilterMock,
            $this->productUpdaterMock,
            $this->productAttributeFilterMock,
            $this->draftValuesWithMissingAttributeFilterMock
        );
    }
}