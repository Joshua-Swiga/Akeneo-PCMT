<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Draft;

use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Service\Draft\ProductModelDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProductModelDraftCreatorTest extends TestCase
{
    /** @var ProductModelDraftCreator */
    private $productModelDraftCreator;

    protected function setUp(): void
    {
        $this->productModelDraftCreator = new ProductModelDraftCreator();
    }

    public function dataCreate(): array
    {
        return [
            [['ATTRIBUTE' => 'VALUE'], ['ATTRIBUTE' => 'VALUE']],
            [[
                'categories' => ['CATEGORY1'],
            ], [
                'categories' => ['CATEGORY1'],
            ]],
            [[
                'categories' => ['NEW_SKIPPED_DRAFTS'],
            ], [
                'categories' => [],
            ]],
        ];
    }

    /**
     * @dataProvider dataCreate
     */
    public function testCreateWhenProductModelExists(array $productData, array $expectedData): void
    {
        $productModel = (new ProductModelBuilder())
            ->withId(143)
            ->build();

        $user = (new UserBuilder())
            ->build();

        $draft = $this->productModelDraftCreator->create(
            $productModel,
            $productData,
            $user
        );

        $this->assertInstanceOf(ExistingProductModelDraft::class, $draft);
        $this->assertEquals($expectedData, $draft->getProductData());
    }

    public function testCreateWhenProductModelDoesNotExist(): void
    {
        $productModel = (new ProductModelBuilder())
            ->build();

        $user = (new UserBuilder())
            ->build();

        $draft = $this->productModelDraftCreator->create(
            $productModel,
            ['ATTRIBUTE' => 'VALUE'],
            $user
        );

        $this->assertInstanceOf(NewProductModelDraft::class, $draft);
        $this->assertEquals(['ATTRIBUTE' => 'VALUE'], $draft->getProductData());
    }
}