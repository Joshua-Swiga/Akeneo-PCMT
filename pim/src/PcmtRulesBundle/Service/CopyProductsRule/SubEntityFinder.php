<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service\CopyProductsRule;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Doctrine\Common\Collections\Collection;

class SubEntityFinder
{
    public function findByAxisAttributes(
        ProductModelInterface $productModel,
        Collection $axisAttributes,
        ProductInterface $sourceProduct
    ): ?EntityWithValuesInterface {
        foreach ($productModel->getProductModels() as $subProductModel) {
            if ($this->compareIfEqual($subProductModel, $sourceProduct, $axisAttributes)) {
                return $subProductModel;
            }
        }

        foreach ($productModel->getProducts() as $subProduct) {
            if ($this->compareIfEqual($subProduct, $sourceProduct, $axisAttributes)) {
                return $subProduct;
            }
        }

        return null;
    }

    private function compareIfEqual(EntityWithValuesInterface $product1, EntityWithValuesInterface $product2, Collection $attributes): bool
    {
        foreach ($attributes as $attribute) {
            $value1 = $product1->getValue($attribute->getCode());
            $value2 = $product2->getValue($attribute->getCode());
            if (!$value1 || !$value2 || !$value1->isEqual($value2)) {
                return false;
            }
        }

        return true;
    }
}