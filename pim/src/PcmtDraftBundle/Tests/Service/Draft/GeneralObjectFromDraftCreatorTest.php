<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Service\Draft\ObjectFromDraftCreatorInterface;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class GeneralObjectFromDraftCreatorTest extends TestCase
{
    /** @var ObjectFromDraftCreatorInterface|MockObject */
    private $productCreatorMock;

    /** @var ObjectFromDraftCreatorInterface|MockObject */
    private $productModelCreatorMock;

    /** @var LoggerInterface|MockObject */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->productCreatorMock = $this->createMock(ObjectFromDraftCreatorInterface::class);
        $this->productModelCreatorMock = $this->createMock(ObjectFromDraftCreatorInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
    }

    public function testGetCreatorThrowsException(): void
    {
        $this->expectException(\Throwable::class);
        $service = $this->getCreatorInstance();
        $draft = $this->createMock(DraftInterface::class);
        $service->getObjectToSave($draft);
    }

    public function testGetObjectToSaveThrowsException(): void
    {
        $this->productCreatorMock->expects($this->once())
            ->method('createNewObject')
            ->willThrowException(new InvalidPropertyException('', '', ''));
        $this->loggerMock->expects($this->once())->method('error');
        $service = $this->getCreatorInstance();
        $draft = (new NewProductDraftBuilder())->build();
        $result = $service->getObjectToSave($draft);
        $this->assertNull($result);
    }

    public function testGetObjectToCompareThrowsException(): void
    {
        $this->productCreatorMock->expects($this->once())
            ->method('createNewObject')
            ->willThrowException(new InvalidPropertyException('', '', ''));
        $this->loggerMock->expects($this->once())->method('error');
        $service = $this->getCreatorInstance();
        $draft = (new NewProductDraftBuilder())->build();
        $result = $service->getObjectToCompare($draft);
        $this->assertNull($result);
    }

    public function testGetObjectToSaveNewProductDraft(): void
    {
        $product = $this->createMock(EntityWithAssociationsInterface::class);
        $draft = $this->createMock(NewProductDraft::class);
        $this->productCreatorMock->expects($this->once())->method('createNewObject')->willReturn($product);
        $service = $this->getCreatorInstance();
        $this->assertSame($product, $service->getObjectToSave($draft));
    }

    public function testGetObjectToSaveExistingProductDraft(): void
    {
        $productMock = $this->createMock(ProductModelInterface::class);
        $collection = new WriteValueCollection();
        $collection->add($this->createMock(ValueInterface::class));
        $productMock->method('getValuesForVariation')->willReturn($collection);
        $draft = $this->createMock(ExistingProductDraft::class);
        $this->productCreatorMock->expects($this->once())->method('createForSaveForDraftForExistingObject')->willReturn($productMock);
        $service = $this->getCreatorInstance();
        $this->assertSame($productMock, $service->getObjectToSave($draft));
    }

    public function testGetObjectToCompareNewProductModelDraft(): void
    {
        $object = $this->createMock(EntityWithAssociationsInterface::class);
        $draft = $this->createMock(NewProductModelDraft::class);
        $this->productModelCreatorMock->expects($this->once())->method('createNewObject')->willReturn($object);
        $service = $this->getCreatorInstance();
        $this->assertSame($object, $service->getObjectToCompare($draft));
    }

    public function testGetObjectToCompareExistingProductDraftNoProduct(): void
    {
        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getObject')->willReturn(null);
        $service = $this->getCreatorInstance();
        $this->productModelCreatorMock->expects($this->never())->method('updateObject');
        $this->productCreatorMock->expects($this->never())->method('updateObject');
        $this->assertNull($service->getObjectToCompare($draft));
    }

    public function testGetObjectToCompareExistingProductDraft(): void
    {
        $productMock = $this->createMock(ProductModelInterface::class);
        $collection = new WriteValueCollection();
        $collection->add($this->createMock(ValueInterface::class));
        $productMock->method('getValuesForVariation')->willReturn($collection);
        $draft = $this->createMock(ExistingProductModelDraft::class);
        $draft->method('getObject')->willReturn($productMock);
        $draft->method('getProductData')->willReturn([
            'values' => [
                'xxx' => 1,
            ],
        ]);
        $service = $this->getCreatorInstance();
        $this->productModelCreatorMock->expects($this->once())->method('updateObject');
        $this->productCreatorMock->expects($this->never())->method('updateObject');
        $this->assertInstanceOf(EntityWithAssociationsInterface::class, $service->getObjectToCompare($draft));
    }

    private function getCreatorInstance(): GeneralObjectFromDraftCreator
    {
        return new GeneralObjectFromDraftCreator(
            $this->productCreatorMock,
            $this->productModelCreatorMock,
            $this->loggerMock
        );
    }
}