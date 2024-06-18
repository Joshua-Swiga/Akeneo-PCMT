<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\Model\Family;
use Doctrine\Common\Collections\Collection;

class ProductBuilder
{
    public const EXAMPLE_ID = 12;

    /** @var ProductInterface */
    private $product;

    public function __construct()
    {
        $this->product = new Product();
        $this->product->setId(self::EXAMPLE_ID);
        $this->product->setFamily((new FamilyBuilder())->build());
    }

    public function withParent(ProductModel $parent): self
    {
        $this->product->setParent($parent);

        return $this;
    }

    public function withId(?int $id): self
    {
        $this->product->setId($id);

        return $this;
    }

    public function withFamily(Family $family): self
    {
        $this->product->setFamily($family);

        return $this;
    }

    public function addValue(ValueInterface $value): self
    {
        $this->product->getValues()->add($value);

        return $this;
    }

    public function withAssociations(Collection $collection): self
    {
        $this->product->setAssociations($collection);

        return $this;
    }

    public function addCategory(CategoryInterface $category): self
    {
        $this->product->addCategory($category);

        return $this;
    }

    public function withIdentifier(string $identifier): self
    {
        $this->product->setIdentifier(ScalarValue::value('sku', $identifier));

        return $this;
    }

    public function build(): Product
    {
        return $this->product;
    }
}
