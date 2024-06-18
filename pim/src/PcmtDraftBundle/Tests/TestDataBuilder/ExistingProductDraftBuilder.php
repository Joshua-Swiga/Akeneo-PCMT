<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductDraft;

class ExistingProductDraftBuilder extends AbstractDraftBuilder
{
    private const EXAMPLE_PRODUCT_DATA = [];
    private const EXAMPLE_DRAFT_ID = 1;

    /** @var ExistingProductDraft */
    private $existingProductDraft;

    public function __construct()
    {
        $this->existingProductDraft = new ExistingProductDraft(
            (new ProductBuilder())->build(),
            self::EXAMPLE_PRODUCT_DATA,
            new \DateTime(),
            (new UserBuilder())->build()
        );

        $this->setDraftId($this->existingProductDraft, self::EXAMPLE_DRAFT_ID);
        $this->existingProductDraft->setStatus(AbstractDraft::STATUS_NEW);
    }

    public function withId(int $id): self
    {
        $this->setDraftId($this->existingProductDraft, $id);

        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->existingProductDraft->setStatus($status);

        return $this;
    }

    public function withUpdatedAt(\DateTime $updatedAt): self
    {
        $this->existingProductDraft->setUpdated($updatedAt);

        return $this;
    }

    public function build(): ExistingProductDraft
    {
        return $this->existingProductDraft;
    }

    public function withProduct(?ProductInterface $product): self
    {
        $this->existingProductDraft->setProduct($product);

        return $this;
    }
}