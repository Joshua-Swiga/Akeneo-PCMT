<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

abstract class AbstractProductModelDraft extends AbstractDraft implements ProductModelDraftInterface
{
    protected function __construct(
        ?UserInterface $author,
        \DateTime $created
    ) {
        $this->author = $author;
        $this->created = $created;
        $this->status = self::STATUS_NEW;
        $this->version = self::DRAFT_VERSION_NEW;

        parent::__construct();
    }

    public function getProductModel(): ?ProductModelInterface
    {
        return $this->productModel;
    }

    public function setProduct(?ProductModelInterface $productModel = null): void
    {
        $this->productModel = $productModel;
    }

    public function getObject(): ?EntityWithAssociationsInterface
    {
        return $this->getProductModel();
    }
}