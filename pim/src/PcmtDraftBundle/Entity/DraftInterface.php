<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use DateTime;

interface DraftInterface
{
    public const DRAFT_VERSION_NEW = 1;

    public function getId(): int;

    public function getType(): string;

    public function getAuthor(): ?UserInterface;

    public function setStatus(int $statusId): void;

    public function getStatus(): int;

    public function setApproved(DateTime $approved): void;

    public function getApproved(): ?DateTime;

    public function setApprovedBy(UserInterface $approvedBy): void;

    public function getApprovedBy(): ?UserInterface;

    public function getObject(): ?EntityWithAssociationsInterface;

    public function getCreatedAt(): DateTime;

    public function getUpdatedAt(): ?DateTime;

    public function approve(UserInterface $approver): void;

    public function reject(): void;

    public function setProductData(array $productData): void;
}