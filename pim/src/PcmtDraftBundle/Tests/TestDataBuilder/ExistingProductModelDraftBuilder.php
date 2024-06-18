<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;

class ExistingProductModelDraftBuilder extends AbstractDraftBuilder
{
    private const EXAMPLE_PRODUCT_MODEL_DATA = [];
    private const EXAMPLE_DRAFT_ID = 1;

    /** @var ExistingProductModelDraft */
    private $existingProductModelDraft;

    public function __construct()
    {
        $this->existingProductModelDraft = new ExistingProductModelDraft(
            (new ProductModelBuilder())->build(),
            self::EXAMPLE_PRODUCT_MODEL_DATA,
            (new UserBuilder())->build(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->setDraftId($this->existingProductModelDraft, self::EXAMPLE_DRAFT_ID);
    }

    public function withId(int $id): self
    {
        $this->setDraftId($this->existingProductModelDraft, $id);

        return $this;
    }

    public function withUpdatedAt(\DateTime $updatedAt): self
    {
        $this->existingProductModelDraft->setUpdated($updatedAt);

        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->existingProductModelDraft->setStatus($status);

        return $this;
    }

    public function build(): ExistingProductModelDraft
    {
        return $this->existingProductModelDraft;
    }
}