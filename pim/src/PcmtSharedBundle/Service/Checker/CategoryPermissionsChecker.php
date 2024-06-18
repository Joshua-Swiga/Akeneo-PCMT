<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Service\Checker;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;

class CategoryPermissionsChecker implements CategoryPermissionsCheckerInterface
{
    public function hasAccessToProduct(string $type, ?CategoryAwareInterface $entity, ?UserInterface $user = null): bool
    {
        return true;
    }
}