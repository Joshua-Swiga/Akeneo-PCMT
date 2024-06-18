<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariantInterface;
use Doctrine\Common\Collections\Collection;

class ProductModelBuilder
{
    /** @var ProductModel */
    private $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productModel->setFamilyVariant(
            (new FamilyVariantBuilder())->build()
        );
    }

    public function withId(int $id): self
    {
        $this->setProductModelId($this->productModel, $id);

        return $this;
    }

    public function withParent(ProductModel $productModel): self
    {
        $this->productModel->setParent($productModel);

        return $this;
    }

    public function withCode(string $code): self
    {
        $this->productModel->setCode($code);

        return $this;
    }

    public function withAssociations(Collection $collection): self
    {
        $this->productModel->setAssociations($collection);

        return $this;
    }

    public function withFamilyVariant(FamilyVariantInterface $familyVariant): self
    {
        $this->productModel->setFamilyVariant($familyVariant);

        return $this;
    }

    public function addValue(ValueInterface $value): self
    {
        $this->productModel->getValues()->add($value);

        return $this;
    }

    public function build(): ProductModel
    {
        return $this->productModel;
    }

    private function setProductModelId(ProductModel $productModel, int $value): void
    {
        $reflection = new \ReflectionClass(get_class($productModel));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($productModel, $value);
    }
}