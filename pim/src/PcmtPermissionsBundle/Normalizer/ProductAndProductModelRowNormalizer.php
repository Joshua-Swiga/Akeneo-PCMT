<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Oro\Bundle\PimDataGridBundle\Normalizer\ProductAndProductModelRowNormalizer as OroProductAndProductModelRowNormalizer;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class ProductAndProductModelRowNormalizer extends OroProductAndProductModelRowNormalizer
{
    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ProductModelRepositoryInterface */
    private $productModelRepository;

    public function setCategoryPermissionsChecker(
        CategoryPermissionsCheckerInterface $categoryPermissionsChecker
    ): void {
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function setProductRepository(ProductRepositoryInterface $productRepository): void
    {
        $this->productRepository = $productRepository;
    }

    public function setProductModelRepository(
        ProductModelRepositoryInterface $productModelRepository
    ): void {
        $this->productModelRepository = $productModelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($row, $format = null, array $context = [])
    {
        $data = parent::normalize($row, $format, $context);

        if (false !== mb_strpos($row->searchId(), 'product_model')) {
            $entity = $this->productModelRepository->findBy(['code' => $row->identifier()])[0];
        } else {
            $entity = $this->productRepository->findBy(['identifier' => $row->identifier()])[0];
        }

        $data['user_ownership'] = $this->categoryPermissionsChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::OWN_LEVEL, $entity);

        return $data;
    }
}
