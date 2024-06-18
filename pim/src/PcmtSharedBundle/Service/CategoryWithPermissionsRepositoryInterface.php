<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtSharedBundle\Service;

interface CategoryWithPermissionsRepositoryInterface
{
    public function getCategoryCodes(string $permissionLevel): ?array;

    public function getCategoryIds(string $permissionLevel): ?array;
}