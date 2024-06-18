<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Doctrine\Common\Collections\Collection;

class ProductBuilder
{
    public const EXAMPLE_ID = 16;

    /** @var ProductInterface */
    private $product;

    public function __construct()
    {
        $this->product = new Product();
        $this->product->setId(self::EXAMPLE_ID);
        $this->product->setFamily((new FamilyBuilder())->build());
    }

    public function withParent(ProductModelInterface $parent): self
    {
        $this->product->setParent($parent);

        return $this;
    }

    public function withId(?int $id): self
    {
        $this->product->setId($id);

        return $this;
    }

    public function withFamily(FamilyInterface $family): self
    {
        $this->product->setFamily($family);

        return $this;
    }

    public function withFamilyVariant(FamilyVariant $familyVariant): self
    {
        $this->product->setFamilyVariant($familyVariant);

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

    public function build(): Product
    {
        return $this->product;
    }
}