<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Associations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Service\Associations\AssociationThroughDraftRemoving;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationCollectionBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationTypeBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ProductAssociationBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ProductModelAssociationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssociationThroughDraftRemovingTest extends TestCase
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

    public function dataRemoveProductExistingDraft(): array
    {
        $product1 = (new ProductBuilder())->withId(111)->build();
        $associationType = (new AssociationTypeBuilder())->build();

        $association = (new ProductAssociationBuilder())
            ->withType($associationType)
            ->withProduct($product1)
            ->build();

        $associations = (new AssociationCollectionBuilder())
            ->withAssociation($association)
            ->build();

        $productToBeChanged = (new ProductBuilder())
            ->withId(56)
            ->withAssociations($associations)
            ->build();

        return [
            [$product1, $productToBeChanged, $associationType],
        ];
    }

    /**
     * @dataProvider dataRemoveProductExistingDraft
     */
    public function testRemoveProductExistingDraft(
        EntityWithAssociationsInterface $objectToBeRemovedFromAssociation,
        ProductInterface $objectToBeChanged,
        AssociationType $associationType
    ): void {
        $this->productSaverMock->expects($this->once())->method('save');

        $draft = (new ExistingProductDraftBuilder())->build();
        $this->draftRepositoryMock->expects($this->once())->method('findOneBy')->willReturn($draft);
        $this->objectFromDraftCreatorMock->expects($this->once())->method('getObjectToCompare')->willReturn($objectToBeChanged);

        $service = $this->getRemovingService();
        $service->remove($objectToBeRemovedFromAssociation, $objectToBeChanged, $associationType);
    }

    /**
     * @dataProvider dataRemoveProductExistingDraft
     */
    public function testRemoveProductSameProduct(
        EntityWithAssociationsInterface $objectToBeRemovedFromAssociation,
        ProductInterface $objectToBeChanged,
        AssociationType $associationType
    ): void {
        $objectToBeChanged->setId($objectToBeRemovedFromAssociation->getId());

        $this->productSaverMock->expects($this->never())->method('save');
        $this->draftRepositoryMock->expects($this->never())->method('findOneBy');
        $this->objectFromDraftCreatorMock->expects($this->never())->method('getObjectToCompare');

        $service = $this->getRemovingService();
        $service->remove($objectToBeRemovedFromAssociation, $objectToBeChanged, $associationType);
    }

    public function dataRemoveProductModel(): array
    {
        $productModel1 = (new ProductModelBuilder())->withId(222)->build();
        $product1 = (new ProductBuilder())->withId(34)->build();
        $associationType = (new AssociationTypeBuilder())->build();

        $association1 = (new ProductAssociationBuilder())
            ->withType($associationType)
            ->withProduct($product1)
            ->build();

        $association2 = (new ProductModelAssociationBuilder())
            ->withType($associationType)
            ->withProductModel($productModel1)
            ->build();

        $associations = (new AssociationCollectionBuilder())
            ->withAssociation($association1)
            ->withAssociation($association2)
            ->build();

        $productModelToBeChanged1 = (new ProductModelBuilder())
            ->withAssociations($associations)
            ->build();

        $productModelToBeChanged2 = (new ProductModelBuilder())
            ->build();

        return [
            [$product1, $productModelToBeChanged1, $associationType, 1],
            [$productModel1, $productModelToBeChanged1, $associationType, 1],
            [$productModel1, $productModelToBeChanged2, $associationType, 0],
        ];
    }

    /**
     * @dataProvider dataRemoveProductModel
     */
    public function testRemoveProductModel(
        EntityWithAssociationsInterface $objectToBeRemovedFromAssociation,
        ProductModelInterface $objectToBeChanged,
        AssociationType $associationType,
        int $expectedSaveCalls
    ): void {
        $this->productModelSaverMock->expects($this->exactly($expectedSaveCalls))->method('save');

        $service = $this->getRemovingService();
        $service->remove($objectToBeRemovedFromAssociation, $objectToBeChanged, $associationType);
    }

    private function getRemovingService(): AssociationThroughDraftRemoving
    {
        return new AssociationThroughDraftRemoving(
            $this->productSaverMock,
            $this->productModelSaverMock,
            $this->objectFromDraftCreatorMock,
            $this->draftRepositoryMock
        );
    }
}