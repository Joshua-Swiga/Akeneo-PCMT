<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class ExistingProductDraft extends AbstractProductDraft implements ExistingObjectDraftInterface
{
    public const TYPE = 'existing product draft';

    public function __construct(
        ProductInterface $product,
        array $productData,
        \DateTime $created,
        ?UserInterface $author = null
    ) {
        $this->product = $product;
        $this->productData = $productData;
        parent::__construct($created, $author);

        $this->setCategories($this->product->getCategories());
    }
}