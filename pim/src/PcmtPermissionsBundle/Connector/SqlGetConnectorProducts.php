<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetConnectorProducts;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use PcmtSharedBundle\Service\CategoryWithPermissionsRepositoryInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SqlGetConnectorProducts implements Query\GetConnectorProducts
{
    /** @var GetConnectorProducts */
    private $originalGetConnectorProducts;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CategoryWithPermissionsRepositoryInterface */
    private $categoryWithPermissionsRepository;

    public function __construct(
        GetConnectorProducts $originalGetConnectorProducts,
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker,
        ProductRepositoryInterface $productRepository,
        CategoryWithPermissionsRepositoryInterface $categoryWithPermissionsRepository
    ) {
        $this->originalGetConnectorProducts = $originalGetConnectorProducts;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
        $this->productRepository = $productRepository;
        $this->categoryWithPermissionsRepository = $categoryWithPermissionsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fromProductQueryBuilder(
        ProductQueryBuilderInterface $pqb,
        int $userId,
        ?array $attributesToFilterOn,
        ?string $channelToFilterOn,
        ?array $localesToFilterOn
    ): ConnectorProductList {
        $pqb->addFilter(
            'categories',
            Query\Filter\Operators::IN_LIST_OR_UNCLASSIFIED,
            $this->categoryWithPermissionsRepository->getCategoryCodes(
                CategoryPermissionsCheckerInterface::VIEW_LEVEL
            )
        );

        return $this->originalGetConnectorProducts->fromProductQueryBuilder(
            $pqb,
            $userId,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );
    }

    public function fromProductIdentifier(string $productIdentifier, int $userId): ConnectorProduct
    {
        $product = $this->productRepository->findOneByIdentifier($productIdentifier);
        $access = $this->categoryPermissionsChecker->hasAccessToProduct(
            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
            $product
        );
        if (!$access) {
            throw new AccessDeniedHttpException('No access');
        }

        return $this->originalGetConnectorProducts->fromProductIdentifier($productIdentifier, $userId);
    }
}
