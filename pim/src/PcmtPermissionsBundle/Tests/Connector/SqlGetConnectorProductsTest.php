<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use PcmtPermissionsBundle\Connector\SqlGetConnectorProducts;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ConnectorProductBuilder;
use PcmtPermissionsBundle\Tests\TestDataBuilder\ConnectorProductListBuilder;
use PcmtSharedBundle\Service\CategoryWithPermissionsRepositoryInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SqlGetConnectorProductsTest extends TestCase
{
    /** @var GetConnectorProducts|MockObject */
    private $originalSqlGetConnectorProductsMock;

    /** @var CategoryPermissionsCheckerInterface|MockObject */
    private $categoryPermissionsCheckerMock;

    /** @var ProductRepositoryInterface|MockObject */
    private $productRepositoryMock;

    /** @var CategoryWithPermissionsRepositoryInterface|MockObject */
    private $categoryWithPermissionsRepositoryMock;

    protected function setUp(): void
    {
        $this->originalSqlGetConnectorProductsMock = $this->createMock(GetConnectorProducts::class);
        $this->categoryPermissionsCheckerMock = $this->createMock(CategoryPermissionsCheckerInterface::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->categoryWithPermissionsRepositoryMock = $this->createMock(CategoryWithPermissionsRepositoryInterface::class);

        parent::setUp();
    }

    /**
     * @dataProvider dataFromProductIdentifier
     */
    public function testFromProductIdentifier(string $identifier, int $userId): void
    {
        $product = (new ConnectorProductBuilder())->build();

        $connector = $this->getSqlGetConnectorProductsInstance();
        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturn(true);
        $this->originalSqlGetConnectorProductsMock->method('fromProductIdentifier')->willReturn($product);
        $result = $connector->fromProductIdentifier($identifier, $userId);
        $this->assertEquals($product, $result);
    }

    /**
     * @dataProvider dataFromProductIdentifier
     */
    public function testFromProductIdentifierNoAccess(string $identifier, int $userId): void
    {
        $connector = $this->getSqlGetConnectorProductsInstance();
        $this->categoryPermissionsCheckerMock->method('hasAccessToProduct')->willReturn(false);
        $this->expectException(AccessDeniedHttpException::class);
        $connector->fromProductIdentifier($identifier, $userId);
    }

    public function dataFromProductIdentifier(): array
    {
        return [
            ['identifier', 11],
        ];
    }

    public function getSqlGetConnectorProductsInstance(): SqlGetConnectorProducts
    {
        return new SqlGetConnectorProducts(
            $this->originalSqlGetConnectorProductsMock,
            $this->categoryPermissionsCheckerMock,
            $this->productRepositoryMock,
            $this->categoryWithPermissionsRepositoryMock
        );
    }

    public function testFromProductQueryBuilder(): void
    {
        $pqbMock = $this->createMock(ProductQueryBuilderInterface::class);

        $productList = (new ConnectorProductListBuilder())->build();

        $this->originalSqlGetConnectorProductsMock->method('fromProductQueryBuilder')->willReturn($productList);

        $connector = $this->getSqlGetConnectorProductsInstance();
        $result = $connector->fromProductQueryBuilder(
            $pqbMock,
            1,
            [],
            'channel',
            []
        );

        $this->assertEquals($productList, $result);
    }
}