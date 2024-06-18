<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

interface ProductDraftInterface extends DraftInterface
{
    public function getProductData(): ?array;

    public function getProduct(): ?ProductInterface;
}