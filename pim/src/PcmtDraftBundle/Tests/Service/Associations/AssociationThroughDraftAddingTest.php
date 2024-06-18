<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Associations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Associations\AssociationThroughDraftAdding;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationTypeBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssociationThroughDraftAddingTest extends TestCase
{
    /** @var SaverInterface|MockObject */
    private $productSaverMock;

    /** @var SaverInterface|MockObject */
    private $productModelSaverMock;

    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $objectFromDraftCreatorMock;

    /** @var DraftRepository|MockObject */
    private $draftRepositoryMock;

    protected function setUp(): void
    {
        $this->productSaverMock = $this->createMock(SaverInterface::class);
        $this->productModelSaverMock = $this->createMock(SaverInterface::class);
        $this->objectFromDraftCreatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->draftRepositoryMock = $this->createMock(DraftRepository::class);
    }

    public function testAddProductExistingDraft(): void
    {
        $objectToBeChanged = (new ProductBuilder())->withId(2234)->build();
        $objectToBeAssociated = (new ProductBuilder())->withId(176)->build();
        $associationType = (new AssociationTypeBuilder())->build();

        $this->productSaverMock->expects($this->once())->method('save');

        $draft = (new ExistingProductDraftBuilder())->build();
        $this->draftRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($draft);
        $this->objectFromDraftCreatorMock->expects($this->once())->method('getObjectToCompare')->willReturn($objectToBeChanged);

        $adding = $this->getAdding();
        $adding->add($objectToBeAssociated, $objectToBeChanged, $associationType);
    }

    public function testAddProductSameProduct(): void
    {
        $objectToBeChanged = (new ProductBuilder())->withId(22)->build();
        $objectToBeAssociated = (new ProductBuilder())->withId(22)->build();
        $associationType = (new AssociationTypeBuilder())->build();

        $this->productSaverMock->expects($this->never())->method('save');
        $this->draftRepositoryMock->expects($this->never())->method('findOneBy');
        $this->objectFromDraftCreatorMock->expects($this->never())->method('getObjectToCompare');

        $adding = $this->getAdding();
        $adding->add($objectToBeAssociated, $objectToBeChanged, $associationType);
    }

    public function dataAddProductModel(): array
    {
        $productModel1 = (new ProductModelBuilder())->withId(234)->build();
        $productModel2 = (new ProductModelBuilder())->withId(1123)->build();
        $product1 = (new ProductBuilder())->withId(456)->build();
        $associationType = (new AssociationTypeBuilder())->build();

        return [
            [$product1, $productModel1, $associationType],
            [$productModel1, $productModel2, $associationType],
        ];
    }

    /**
     * @dataProvider dataAddProductModel
     */
    public function testAddProductModel(
        EntityWithAssociationsInterface $objectToBeAssociated,
        ProductModelInterface $objectToBeChanged,
        AssociationType $associationType
    ): void {
        $this->productModelSaverMock->expects($this->once())->method('save');

        $adding = $this->getAdding();
        $adding->add($objectToBeAssociated, $objectToBeChanged, $associationType);
    }

    private function getAdding(): AssociationThroughDraftAdding
    {
        return new AssociationThroughDraftAdding(
            $this->productSaverMock,
            $this->productModelSaverMock,
            $this->objectFromDraftCreatorMock,
            $this->draftRepositoryMock
        );
    }
}