<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Service\Helper;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtDraftBundle\Service\Helper\SpecialCategoryUpdater;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpecialCategoryUpdaterTest extends TestCase
{
    /** @var ObjectUpdaterInterface|MockObject */
    private $objectUpdaterMock;

    protected function setUp(): void
    {
        $this->objectUpdaterMock = $this->createMock(ObjectUpdaterInterface::class);
    }

    private function getSpecialCategoryUpdater(): SpecialCategoryUpdater
    {
        return new SpecialCategoryUpdater($this->objectUpdaterMock);
    }

    public function testAddSpecialCategory(): void
    {
        $productModel = (new ProductModelBuilder())->build();

        $data = [
            'values'     => [],
            'categories' => ['NEW_SKIPPED_DRAFTS'],
        ];
        $this->objectUpdaterMock->expects($this->once())->method('update')->with($productModel, $data);

        $updater = $this->getSpecialCategoryUpdater();
        $updater->addSpecialCategory($productModel);
    }

    public function testAddSpecialCategoryThrowsException(): void
    {
        $product = (new ProductBuilder())->build();

        $data = [
            'values'     => [],
            'categories' => ['NEW_SKIPPED_DRAFTS'],
        ];
        $exception = $this->createMock(InvalidPropertyException::class);
        $this->objectUpdaterMock->expects($this->once())->method('update')->with($product, $data)->willThrowException($exception);

        $updater = $this->getSpecialCategoryUpdater();
        $updater->addSpecialCategory($product);
    }
}