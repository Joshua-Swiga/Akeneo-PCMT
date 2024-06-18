<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Connector;

use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use PcmtPermissionsBundle\Connector\SqlGetConnectorProductModels;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ConnectorProductModelBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ConnectorProductModelListBuilder;
use PcmtSharedBundle\Service\CategoryWithPermissionsRepositoryInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SqlGetConnectorProductModelsTest extends TestCase
{
    /** @var GetConnectorProductModels|MockObject */
    private $originalSqlGetConnectorProductModelsMock;

    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $categoryPermissionsCheckerMock;

    /** @var ProductModelRepositoryInterface|MockObject */
    private $productModelRepositoryMock;

    /** @var CategoryWithPermissionsRepositoryInterface|MockObject */
    private $categoryWithPermissionsRepositoryMock;

    protected function setUp(): void
    {
        $this->originalSqlGetConnectorProductModelsMock = $this->createMock(GetConnectorProductModels::class);
        $this->categoryPermissionsCheckerMock = $this->createMock(CategoryPermissionsCheckerInterface::class);
        $this->productModelRepositoryMock = $this->createMock(ProductModelRepositoryInterface::class);
        $this->categoryWithPermissionsRepositoryMock = $this->createMock(CategoryWithPermissionsRepositoryInterface::class);

        parent::setUp();
    }

    /**
     * @dataProvider dataFromProductIdentifier
     */
    public function testFromProductModelCode(string $identifier, int $userId): void
    {
        $productModel = (new ConnectorProductModelBuilder())->build();

        $connector = $this->getSqlGetConnectorProductModelsInstance();
        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturn(true);
        $this->originalSqlGetConnectorProductModelsMock->method('fromProductModelCode')->willReturn($productModel);
        $result = $connector->fromProductModelCode($identifier, $userId);
        $this->assertEquals($productModel, $result);
    }

    /**
     * @dataProvider dataFromProductIdentifier
     */
    public function testFromProductModelCodeNoAccess(string $identifier, int $userId): void
    {
        $connector = $this->getSqlGetConnectorProductModelsInstance();
        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $connector->fromProductModelCode($identifier, $userId);
    }

    public function dataFromProductIdentifier(): array
    {
        return [
            ['identifier', 11],
        ];
    }

    public function getSqlGetConnectorProductModelsInstance(): SqlGetConnectorProductModels
    {
        return new SqlGetConnectorProductModels(
            $this->originalSqlGetConnectorProductModelsMock,
            $this->categoryPermissionsCheckerMock,
            $this->productModelRepositoryMock,
            $this->categoryWithPermissionsRepositoryMock
        );
    }

    public function testFromProductQueryBuilder(): void
    {
        $pqbMock = $this->createMock(ProductQueryBuilderInterface::class);

        $productModelList = (new ConnectorProductModelListBuilder())->build();

        $this->originalSqlGetConnectorProductModelsMock->method('fromProductQueryBuilder')->willReturn($productModelList);

        $connector = $this->getSqlGetConnectorProductModelsInstance();
        $result = $connector->fromProductQueryBuilder(
            $pqbMock,
            1,
            [],
            'channel',
            []
        );

        $this->assertEquals($productModelList, $result);
    }
}