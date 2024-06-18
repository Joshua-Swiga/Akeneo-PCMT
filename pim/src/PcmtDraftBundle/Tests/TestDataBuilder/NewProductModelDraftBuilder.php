<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;

class NewProductModelDraftBuilder extends AbstractDraftBuilder
{
    private const EXAMPLE_PRODUCT_MODEL_DATA = [];
    private const EXAMPLE_DRAFT_ID = 1;

    /** @var NewProductModelDraft */
    private $newProductModelDraft;

    public function __construct()
    {
        $this->newProductModelDraft = new NewProductModelDraft(
            self::EXAMPLE_PRODUCT_MODEL_DATA,
            (new UserBuilder())->build(),
            new \DateTime(),
            AbstractDraft::STATUS_NEW
        );

        $this->setDraftId($this->newProductModelDraft, self::EXAMPLE_DRAFT_ID);
    }

    public function withId(int $id): self
    {
        $this->setDraftId($this->newProductModelDraft, $id);

        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->newProductModelDraft->setStatus($status);

        return $this;
    }

    public function build(): NewProductModelDraft
    {
        return $this->newProductModelDraft;
    }
}