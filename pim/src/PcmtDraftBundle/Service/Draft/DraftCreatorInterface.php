<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtDraftBundle\Entity\AbstractDraft;

interface DraftCreatorInterface
{
    public const CATEGORY_FOR_BASE_PRODUCTS = 'NEW_SKIPPED_DRAFTS';

    /**
     * @param ProductInterface|ProductModelInterface $baseEntity
     */
    public function create($baseEntity, array $productData, ?UserInterface $author = null): AbstractDraft;
}