<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Draft;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use PcmtDraftBundle\Entity\DraftInterface;

interface ObjectFromDraftCreatorInterface
{
    public function createNewObject(DraftInterface $draft): ?EntityWithAssociationsInterface;

    public function updateObject(EntityWithAssociationsInterface $entity, array $data): void;

    public function createForSaveForDraftForExistingObject(DraftInterface $draft): ?EntityWithAssociationsInterface;
}