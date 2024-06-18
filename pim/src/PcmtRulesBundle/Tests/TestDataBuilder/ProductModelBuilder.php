<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Doctrine\Common\Collections\Collection;

class ProductModelBuilder
{
    public const EXAMPLE_CODE = 'a124';

    /** @var ProductModelInterface */
    private $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productModel->setCode(self::EXAMPLE_CODE);
    }

    public function addValue(ValueInterface $value): self
    {
        $this->productModel->addValue($value);

        return $this;
    }

    public function withAssociations(Collection $collection): self
    {
        $this->productModel->setAssociations($collection);

        return $this;
    }

    public function addSubProductModel(ProductModelInterface $productModel): self
    {
        $this->productModel->addProductModel($productModel);

        return $this;
    }

    public function addProductVariant(ProductInterface $product): self
    {
        $this->productModel->addProduct($product);

        return $this;
    }

    public function withFamilyVariant(FamilyVariant $familyVariant): self
    {
        $this->productModel->setFamilyVariant($familyVariant);

        return $this;
    }

    public function build(): ProductModelInterface
    {
        return $this->productModel;
    }
}