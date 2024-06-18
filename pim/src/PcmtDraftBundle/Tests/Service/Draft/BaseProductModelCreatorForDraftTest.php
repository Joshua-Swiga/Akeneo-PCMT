<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\ProductModelUpdater;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtDraftBundle\Service\Draft\BaseProductModelCreatorForDraft;
use PcmtDraftBundle\Tests\TestDataBuilder\FamilyVariantBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\VariantAttributeSetBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BaseProductModelCreatorForDraftTest extends TestCase
{
    private const TEST_PRODUCT_MODEL_CODE = 'TEST_CODE';

    /** @var BaseProductModelCreatorForDraft */
    private $baseProductModelCreator;

    /** @var ProductModelUpdater|MockObject */
    private $productModelUpdaterMock;

    /** @var NormalizerInterface|MockObject */
    private $standardNormalizerMock;

    protected function setUp(): void
    {
        $this->productModelUpdaterMock = $this->createMock(ProductModelUpdater::class);
        $this->standardNormalizerMock = $this->createMock(NormalizerInterface::class);

        $this->baseProductModelCreator = new BaseProductModelCreatorForDraft(
            $this->productModelUpdaterMock,
            $this->standardNormalizerMock
        );

        $variantAttributeSetMock = $this->createMock(VariantAttributeSetInterface::class);

        $variantAttributeSetMock
            ->method('getAxes')
            ->willReturn(
                new ArrayCollection(
                    [
                        $this->createMock(AttributeInterface::class),
                    ]
                )
            );
    }

    public function testCreate(): void
    {
        $this->productModelUpdaterMock
            ->expects($this->once())
            ->method('update');

        $processedProductModelMock = (new ProductModelBuilder())
            ->withCode(self::TEST_PRODUCT_MODEL_CODE)
            ->withFamilyVariant((new FamilyVariantBuilder())->build())
            ->build();

        $baseProductModel = $this->baseProductModelCreator->create($processedProductModelMock);

        $this->assertInstanceOf(ProductModelInterface::class, $baseProductModel);
    }

    public function testCreateWhenProductModelIsNotRoot(): void
    {
        $this->productModelUpdaterMock
            ->expects($this->once())
            ->method('update');

        $this->standardNormalizerMock
            ->expects($this->once())
            ->method('normalize');

        $processedProductModelMock = (new ProductModelBuilder())
            ->withParent((new ProductModelBuilder())->build())
            ->withCode(self::TEST_PRODUCT_MODEL_CODE)
            ->withFamilyVariant(
                (new FamilyVariantBuilder())
                    ->withVariantAttributeSet(
                        (new VariantAttributeSetBuilder())
                            ->withLevel(1)
                            ->build()
                    )
                    ->build()
            )
            ->build();

        $baseProductModel = $this->baseProductModelCreator->create($processedProductModelMock);

        $this->assertInstanceOf(ProductModelInterface::class, $baseProductModel);
    }
}