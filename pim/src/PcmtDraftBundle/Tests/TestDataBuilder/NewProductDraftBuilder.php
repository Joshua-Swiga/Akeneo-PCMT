<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use PcmtDraftBundle\Entity\NewProductDraft;
use Symfony\Component\Security\Core\User\UserInterface;

class NewProductDraftBuilder extends AbstractDraftBuilder
{
    private const EXAMPLE_PRODUCT_DATA = [];
    private const EXAMPLE_DRAFT_ID = 1;

    /** @var NewProductDraft */
    private $newProductDraft;

    public function __construct()
    {
        $this->newProductDraft = new NewProductDraft(
            self::EXAMPLE_PRODUCT_DATA,
            new \DateTime(),
            (new UserBuilder())->build()
        );

        $this->setDraftId($this->newProductDraft, self::EXAMPLE_DRAFT_ID);
    }

    public function withId(int $id): self
    {
        $this->setDraftId($this->newProductDraft, $id);

        return $this;
    }

    public function withStatus(int $status): self
    {
        $this->newProductDraft->setStatus($status);

        return $this;
    }

    public function withOwner(UserInterface $user): self
    {
        $this->author = $user;

        return $this;
    }

    public function withProductData(array $productData): self
    {
        $this->productData = $productData;

        return $this;
    }

    public function build(): NewProductDraft
    {
        return $this->newProductDraft;
    }
}