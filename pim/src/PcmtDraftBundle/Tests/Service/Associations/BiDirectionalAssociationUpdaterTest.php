<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Service\Associations;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Service\Associations\AssociationThroughDraftAdding;
use PcmtDraftBundle\Service\Associations\AssociationThroughDraftRemoving;
use PcmtDraftBundle\Service\Associations\BiDirectionalAssociationUpdater;
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

class BiDirectionalAssociationUpdaterTest extends TestCase
{
    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $objectFromDraftCreatorMock;

    /** @var AssociationThroughDraftAdding|MockObject */
    private $associationThroughDraftAddingMock;

    /** @var AssociationThroughDraftRemoving|MockObject */
    private $associationThroughDraftRemovingMock;

    protected function setUp(): void
    {
        $this->objectFromDraftCreatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->associationThroughDraftAddingMock = $this->createMock(AssociationThroughDraftAdding::class);
        $this->associationThroughDraftRemovingMock = $this->createMock(AssociationThroughDraftRemoving::class);
    }

    public function dataRemoveAssociations(): array
    {
        $associationTypeCode1 = 'example_code_1';
        $associationTypeCode2 = 'example_code_2';
        $productIdentifier2 = 'sdfdgfhdh';
        $productModelCode1 = 'sdfdsdsgdfhghg';

        $connectedProduct1 = (new ProductBuilder())->withId(10)->withIdentifier($productIdentifier2 . 'other')->build();
        $connectedProduct2 = (new ProductBuilder())->withId(12)->withIdentifier($productIdentifier2)->build();
        $connectedProductModel1 = (new ProductModelBuilder())->withId(20)->withCode($productModelCode1)->build();
        $connectedProductModel2 = (new ProductModelBuilder())->withId(22)->withCode($productModelCode1 . 'other')->build();

        $associationType1 = (new AssociationTypeBuilder())->withCode($associationTypeCode1)->build();
        $associationType2 = (new AssociationTypeBuilder())->withCode($associationTypeCode2)->build();
        $association1 = (new ProductAssociationBuilder())
            ->withType($associationType1)
            ->withProduct($connectedProduct1)
            ->withProduct($connectedProduct2)
            ->build();
        $association1_1 = (new ProductAssociationBuilder())
            ->withType($associationType1)
            ->withProduct($connectedProduct2)
            ->build();
        $association2 = (new ProductModelAssociationBuilder())
            ->withType($associationType2)
            ->withProductModel($connectedProductModel1)
            ->withProductModel($connectedProductModel2)
            ->build();
        $association2_1 = (new ProductModelAssociationBuilder())
            ->withType($associationType2)
            ->withProductModel($connectedProductModel1)
            ->build();

        $product = (new ProductBuilder())->withAssociations(
            (new AssociationCollectionBuilder())
                ->withAssociation($association1)
                ->withAssociation($association2)
                ->build()
        )->build();
        $draft = (new ExistingProductDraftBuilder())->withProduct($product)->build();

        $objectToCompare = (new ProductBuilder())->withAssociations(
            (new AssociationCollectionBuilder())
                ->withAssociation($association1_1)
                ->withAssociation($association2_1)
                ->build()
        )->build();

        return [
            [$draft, $product, 0],
            [$draft, $objectToCompare, 2],
        ];
    }

    /**
     * @dataProvider dataRemoveAssociations
     */
    public function testRemoveAssociations(DraftInterface $draft, EntityWithAssociationsInterface $objectToCompare, int $expectedCallsToRemove): void
    {
        $this->objectFromDraftCreatorMock->method('getObjectToCompare')->willReturn($objectToCompare);

        $this->associationThroughDraftRemovingMock->expects($this->exactly($expectedCallsToRemove))->method('remove');

        $updater = $this->getUpdater();
        $updater->removeAssociations($draft);
    }

    public function dataAddNewAssociations(): array
    {
        $associationTypeCode1 = 'example_code_1';
        $associationTypeCode2 = 'example_code_2';
        $productIdentifier2 = 'sdfdgfhdh';
        $productModelCode1 = 'sdfdsdsgdfhghg';

        $connectedProduct1 = (new ProductBuilder())->withId(10)->withIdentifier($productIdentifier2 . 'other')->build();
        $connectedProduct2 = (new ProductBuilder())->withId(12)->withIdentifier($productIdentifier2)->build();
        $connectedProductModel1 = (new ProductModelBuilder())->withId(20)->withCode($productModelCode1)->build();
        $connectedProductModel2 = (new ProductModelBuilder())->withId(22)->withCode($productModelCode1 . 'other')->build();

        $associationType1 = (new AssociationTypeBuilder())->withCode($associationTypeCode1)->build();
        $associationType2 = (new AssociationTypeBuilder())->withCode($associationTypeCode2)->build();
        $association1 = (new ProductAssociationBuilder())
            ->withType($associationType1)
            ->withProduct($connectedProduct1)
            ->withProduct($connectedProduct2)
            ->build();
        $association2 = (new ProductModelAssociationBuilder())
            ->withType($associationType2)
            ->withProductModel($connectedProductModel1)
            ->withProductModel($connectedProductModel2)
            ->build();

        $product = (new ProductBuilder())->withAssociations(
            (new AssociationCollectionBuilder())
                ->withAssociation($association1)
                ->withAssociation($association2)
                ->build()
        )->build();
        $draft = (new ExistingProductDraftBuilder())->withProduct($product)->build();

        $associationData = [
            $associationTypeCode1 => [
                'products_bi_directional' => [
                    $productIdentifier2,
                ],
                'product_models_bi_directional' => [],
            ],
            $associationTypeCode2 => [
                'products_bi_directional'       => [],
                'product_models_bi_directional' => [
                    $productModelCode1,
                ],
            ],
        ];

        return [
            [$draft, $associationData],
        ];
    }

    /**
     * @dataProvider dataAddNewAssociations
     */
    public function testAddNewAssociations(DraftInterface $draft, array $associationData): void
    {
        $this->objectFromDraftCreatorMock->method('getObjectToCompare')->willReturn($draft->getObject());

        $this->associationThroughDraftAddingMock->expects($this->exactly(2))->method('add');

        $updater = $this->getUpdater();
        $updater->addNewAssociations($draft, $associationData);
    }

    /**
     * @dataProvider dataAddNewAssociations
     */
    public function testAddNewAssociationsEmptyParameter(DraftInterface $draft, array $associationData): void
    {
        $this->objectFromDraftCreatorMock->method('getObjectToCompare')->willReturn($draft->getObject());

        $this->associationThroughDraftAddingMock->expects($this->exactly(0))->method('add');

        $updater = $this->getUpdater();
        $updater->addNewAssociations($draft, []);
    }

    private function getUpdater(): BiDirectionalAssociationUpdater
    {
        return new BiDirectionalAssociationUpdater(
            $this->objectFromDraftCreatorMock,
            $this->associationThroughDraftAddingMock,
            $this->associationThroughDraftRemovingMock
        );
    }
}