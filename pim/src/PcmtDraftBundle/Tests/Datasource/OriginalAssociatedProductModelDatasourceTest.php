<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\PimDataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use PcmtDraftBundle\Datasource\OriginalAssociatedProductModelDatasource;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Repository\DraftRepository;
use PcmtDraftBundle\Tests\TestDataBuilder\DatagridBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationCollectionBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\AssociationTypeBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ProductAssociationBuilder;
use PcmtSharedBundle\Tests\TestDataBuilder\ProductModelAssociationBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OriginalAssociatedProductModelDatasourceTest extends TestCase
{
    /** @var OriginalAssociatedProductModelDatasource */
    private $originalAssociatedProductModelDatasource;

    /** @var ObjectManager|MockObject */
    private $objectManagerMock;

    /** @var ProductQueryBuilderFactoryInterface|MockObject */
    private $productQueryBuilderFactoryMock;

    /** @var NormalizerInterface|MockObject */
    private $serializerMock;

    /** @var FilterEntityWithValuesSubscriber|MockObject */
    private $filterEntityWithValuesSubscriberMock;

    /** @var DraftRepository|MockObject */
    private $draftRepositoryMock;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(ObjectManager::class);
        $this->productQueryBuilderFactoryMock = $this->createMock(ProductQueryBuilderFactoryInterface::class);
        $this->serializerMock = $this->createMock(NormalizerInterface::class);
        $this->filterEntityWithValuesSubscriberMock = $this->createMock(FilterEntityWithValuesSubscriber::class);
        $this->draftRepositoryMock = $this->createMock(DraftRepository::class);

        $this->objectManagerMock->method('getRepository')->with(AbstractDraft::class)->willReturn(
            $this->draftRepositoryMock
        );

        $this->originalAssociatedProductModelDatasource = new OriginalAssociatedProductModelDatasource(
            $this->objectManagerMock,
            $this->productQueryBuilderFactoryMock,
            $this->serializerMock,
            $this->filterEntityWithValuesSubscriberMock
        );
    }

    public function testGetResultWhenAssociationsIsNull(): void
    {
        $grid = (new DatagridBuilder())->build();
        $baseProductModel = (new ProductModelBuilder())->build();
        $draft = (new ExistingProductDraftBuilder())->build();

        $this->draftRepositoryMock->method('findOneBy')->with(
            [
                'status'  => AbstractDraft::STATUS_NEW,
                'product' => $baseProductModel->getId(),
            ]
        )->willReturn($draft);

        $this->originalAssociatedProductModelDatasource->process(
            $grid,
            [
                'locale_code'                  => 'test',
                'scope_code'                   => 'test',
                PagerExtension::PER_PAGE_PARAM => 25,
                'current_product'              => $baseProductModel,
                'association_type_id'          => 1,
            ]
        );
        $results = $this->originalAssociatedProductModelDatasource->getResults();

        $this->assertEquals(
            [
                'totalRecords' => 0,
                'data'         => [],
            ],
            $results
        );
    }

    public function testGetResultsWhenSourceProductModelIsNotInstanceOfProductModelInterface(): void
    {
        $grid = (new DatagridBuilder())->build();
        $baseProductModel = (new ProductModelBuilder())->build();
        $draft = (new ExistingProductDraftBuilder())->build();

        $this->draftRepositoryMock->method('findOneBy')->with(
            [
                'status'  => AbstractDraft::STATUS_NEW,
                'product' => $baseProductModel->getId(),
            ]
        )->willReturn($draft);

        $this->expectException(InvalidObjectException::class);

        $this->originalAssociatedProductModelDatasource->process(
            $grid,
            [
                'locale_code'                  => 'test',
                'scope_code'                   => 'test',
                PagerExtension::PER_PAGE_PARAM => 25,
                'current_product'              => (new ProductBuilder())->build(),
                'association_type_id'          => 1,
            ]
        );
        $this->originalAssociatedProductModelDatasource->getResults();
    }

    public function testGetResultsWhenSourceProductIsNull(): void
    {
        $grid = (new DatagridBuilder())->build();
        $baseProductModel = (new ProductModelBuilder())->build();
        $draft = (new ExistingProductDraftBuilder())->build();

        $this->draftRepositoryMock->method('findOneBy')->with(
            [
                'status'  => AbstractDraft::STATUS_NEW,
                'product' => $baseProductModel->getId(),
            ]
        )->willReturn($draft);

        $this->expectException(InvalidObjectException::class);

        $this->originalAssociatedProductModelDatasource->process(
            $grid,
            [
                'locale_code'                  => 'test',
                'scope_code'                   => 'test',
                PagerExtension::PER_PAGE_PARAM => 25,
                'current_product'              => null,
                'association_type_id'          => 1,
            ]
        );
        $this->originalAssociatedProductModelDatasource->getResults();
    }

    public function testGetResultsWhenSourceProductHasOneAssociation(): void
    {
        $associatedProductsMock = $this->createMock(CursorInterface::class);
        $productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);

        $this->productQueryBuilderFactoryMock->method('create')->willReturn($productQueryBuilderMock);

        $productQueryBuilderMock->method('execute')->willReturn($associatedProductsMock);

        $associationTypeId = 1;

        $grid = (new DatagridBuilder())->build();
        $baseProductModel = (new ProductModelBuilder())->withAssociations(
            (new AssociationCollectionBuilder())->withAssociation(
                (new ProductAssociationBuilder())->withType(
                    (new AssociationTypeBuilder())->withId($associationTypeId)->build()
                )->build()
            )->build()
        )->build();
        $draft = (new ExistingProductDraftBuilder())->build();

        $this->draftRepositoryMock->method('findOneBy')->with(
            [
                'status'  => AbstractDraft::STATUS_NEW,
                'product' => $baseProductModel->getId(),
            ]
        )->willReturn($draft);

        $this->originalAssociatedProductModelDatasource->process(
            $grid,
            [
                'locale_code'                  => 'test',
                'scope_code'                   => 'test',
                PagerExtension::PER_PAGE_PARAM => 25,
                'current_product'              => $baseProductModel,
                'association_type_id'          => $associationTypeId,
            ]
        );
        $this->originalAssociatedProductModelDatasource->setParameters(['dataLocale' => 'en']);
        $results = $this->originalAssociatedProductModelDatasource->getResults();

        $this->assertEquals(
            [
                'totalRecords' => 0,
                'data'         => [],
            ],
            $results
        );
    }

    public function testGetResultsWhenSourceProductHasOneAssociationAndParentAssociationIsNotNull(): void
    {
        $associatedProductsMock = $this->createMock(CursorInterface::class);
        $productQueryBuilderMock = $this->createMock(ProductQueryBuilderInterface::class);

        $this->productQueryBuilderFactoryMock->method('create')->willReturn($productQueryBuilderMock);

        $productQueryBuilderMock->method('execute')->willReturn($associatedProductsMock);

        $associationTypeId = 1;

        $grid = (new DatagridBuilder())->build();
        $baseProductModel = (new ProductModelBuilder())->withAssociations(
            (new AssociationCollectionBuilder())->withAssociation(
                (new ProductAssociationBuilder())->withType(
                    (new AssociationTypeBuilder())->withId($associationTypeId)->build()
                )->build()
            )->build()
        )->withParent(
            (new ProductModelBuilder())->withAssociations(
                (new AssociationCollectionBuilder())->withAssociation(
                    (new ProductModelAssociationBuilder())->withType(
                        (new AssociationTypeBuilder())->withId($associationTypeId)->build()
                    )->build()
                )->build()
            )->build()
        )->build();
        $draft = (new ExistingProductDraftBuilder())->build();

        $this->draftRepositoryMock->method('findOneBy')->with(
            [
                'status'  => AbstractDraft::STATUS_NEW,
                'product' => $baseProductModel->getId(),
            ]
        )->willReturn($draft);

        $this->originalAssociatedProductModelDatasource->process(
            $grid,
            [
                'locale_code'                  => 'test',
                'scope_code'                   => 'test',
                PagerExtension::PER_PAGE_PARAM => 25,
                'current_product'              => $baseProductModel,
                'association_type_id'          => $associationTypeId,
            ]
        );
        $this->originalAssociatedProductModelDatasource->setParameters(['dataLocale' => 'en']);
        $results = $this->originalAssociatedProductModelDatasource->getResults();

        $this->assertEquals(
            [
                'totalRecords' => 0,
                'data'         => [],
            ],
            $results
        );
    }
}