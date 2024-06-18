<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

interface DraftRepositoryInterface extends ObjectRepository
{
    public function findWithPermissionAndStatus(int $statusId, int $offset = 0, ?int $limit = null): array;

    public function countWithPermissionAndStatus(int $statusId): int;
}