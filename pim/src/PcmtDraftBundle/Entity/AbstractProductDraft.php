<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

abstract class AbstractProductDraft extends AbstractDraft implements ProductDraftInterface
{
    protected function __construct(
        \DateTime $created,
        ?UserInterface $author = null
    ) {
        $this->author = $author;
        $this->created = $created;
        $this->status = self::STATUS_NEW;
        $this->version = self::DRAFT_VERSION_NEW;

        parent::__construct();
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function setProduct(?ProductInterface $product = null): void
    {
        $this->product = $product;
    }

    public function getObject(): ?EntityWithAssociationsInterface
    {
        return $this->getProduct();
    }
}