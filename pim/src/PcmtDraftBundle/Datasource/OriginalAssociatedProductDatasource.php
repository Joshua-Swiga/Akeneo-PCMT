<?php

declare(strict_types=1);

/**
 * New associated product datasource.
 * Overrides the original one to fix problems with displaying products and product models.
 *
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\PimDataGridBundle\Datasource\AssociatedProductDatasource as AssociatedProductDatasourceOriginal;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;

/**
 * Original class copyrights:
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class OriginalAssociatedProductDatasource extends AssociatedProductDatasourceOriginal
{
    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $sourceProduct = $this->getConfiguration('current_product', false);
        if (!$sourceProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($sourceProduct, ProductInterface::class);
        }

        return $this->getResultsForProduct($sourceProduct);
    }

    protected function getResultsForProduct(EntityWithAssociationsInterface $sourceProduct): array
    {
        $association = $this->getAssociation($sourceProduct, (int) $this->getConfiguration('association_type_id'));
        if (null === $association) {
            return [
                'totalRecords' => 0,
                'data'         => [],
            ];
        }

        $associatedProductsIds = $this->getAssociatedProductIds($association);
        $associatedProductModelsIds = $this->getAssociatedProductModelIds($association);

        $limit = (int) $this->getConfiguration(PagerExtension::PER_PAGE_PARAM, false);
        $locale = $this->getConfiguration('locale_code');
        $scope = $this->getConfiguration('scope_code');
        $from = null !== $this->getConfiguration('from', false) ?
            (int) $this->getConfiguration('from', false) : 0;

        $associatedProductsIdsFromParent = [];
        $associatedProductModelsIdsFromParent = [];
        $parentAssociation = $this->getParentAssociation($sourceProduct, $this->getConfiguration('association_type_id'));
        if (null !== $parentAssociation) {
            $associatedProductsIdsFromParent = $this->getAssociatedProductIds($parentAssociation);
            $associatedProductModelsIdsFromParent = $this->getAssociatedProductModelIds($parentAssociation);
        }

        $associatedProducts = $this->getAssociatedProducts(
            $associatedProductsIds,
            $limit,
            $from,
            $locale,
            $scope
        );

        $normalizedAssociatedProducts = $this->normalizeProductsAndProductModels(
            $associatedProducts,
            $associatedProductsIdsFromParent,
            $locale,
            $scope
        );

        $productCount = 0;
        foreach ($associatedProducts as $product) {
            if ($product) {
                $productCount++;
            }
        }

        $productModelLimit = $limit - $productCount;
        $normalizedAssociatedProductModels = [];
        if ($productModelLimit > 0) {
            $productModelFrom = $from - count($associatedProductsIds) + $productCount;
            $associatedProductModels = $this->getAssociatedProductModels(
                $associatedProductModelsIds,
                $productModelLimit,
                max($productModelFrom, 0),
                $locale,
                $scope
            );

            $normalizedAssociatedProductModels = $this->normalizeProductsAndProductModels(
                $associatedProductModels,
                $associatedProductModelsIdsFromParent,
                $locale,
                $scope
            );
        }

        $rows = ['totalRecords' => count($associatedProductsIds) + count($associatedProductModelsIds)];
        $rows['data'] = array_merge($normalizedAssociatedProducts, $normalizedAssociatedProductModels);

        return $rows;
    }

    protected function getAssociation(ProductInterface $sourceProduct, int $associationTypeId): ?AssociationInterface
    {
        foreach ($sourceProduct->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getId() === $associationTypeId) {
                return $association;
            }
        }

        return null;
    }
}
