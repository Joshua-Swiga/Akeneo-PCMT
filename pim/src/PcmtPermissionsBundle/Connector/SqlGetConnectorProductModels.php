<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\GetConnectorProductModels;
use Akeneo\Pim\Enrichment\Component\Product\Query;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use PcmtSharedBundle\Service\CategoryWithPermissionsRepositoryInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SqlGetConnectorProductModels implements GetConnectorProductModels
{
    /** @var GetConnectorProductModels */
    private $originalGetConnectorProductModels;

    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    /** @var CategoryWithPermissionsRepositoryInterface */
    private $categoryWithPermissionsRepository;

    public function __construct(
        GetConnectorProductModels $originalGetConnectorProductModels,
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker,
        ProductModelRepositoryInterface $productModelRepository,
        CategoryWithPermissionsRepositoryInterface $categoryWithPermissionsRepository
    ) {
        $this->originalGetConnectorProductModels = $originalGetConnectorProductModels;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
        $this->productModelRepository = $productModelRepository;
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
    ): ConnectorProductModelList {
        $pqb->addFilter(
            'categories',
            Query\Filter\Operators::IN_LIST_OR_UNCLASSIFIED,
            $this->categoryWithPermissionsRepository->getCategoryCodes(
                CategoryPermissionsCheckerInterface::VIEW_LEVEL
            )
        );

        return $this->originalGetConnectorProductModels->fromProductQueryBuilder(
            $pqb,
            $userId,
            $attributesToFilterOn,
            $channelToFilterOn,
            $localesToFilterOn
        );
    }

    public function fromProductModelCode(string $productIdentifier, int $userId): ConnectorProductModel
    {
        $product = $this->productModelRepository->findOneByIdentifier($productIdentifier);
        $access = $this->categoryPermissionsChecker->hasAccessToProduct(
            CategoryPermissionsCheckerInterface::VIEW_LEVEL,
            $product
        );
        if (!$access) {
            throw new AccessDeniedHttpException('No access');
        }

        return $this->originalGetConnectorProductModels->fromProductModelCode($productIdentifier, $userId);
    }
}
