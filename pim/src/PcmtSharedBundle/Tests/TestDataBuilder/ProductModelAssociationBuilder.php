<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelAssociation;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;

class ProductModelAssociationBuilder
{
    /** @var ProductModelAssociation */
    private $association;

    public function __construct()
    {
        $this->association = new ProductModelAssociation();
        $this->withType((new AssociationTypeBuilder())->build());
    }

    public function withType(AssociationType $type): self
    {
        $this->association->setAssociationType($type);

        return $this;
    }

    public function withProductModel(ProductModelInterface $productModel): self
    {
        $this->association->addProductModel($productModel);

        return $this;
    }

    public function build(): ProductModelAssociation
    {
        return $this->association;
    }
}