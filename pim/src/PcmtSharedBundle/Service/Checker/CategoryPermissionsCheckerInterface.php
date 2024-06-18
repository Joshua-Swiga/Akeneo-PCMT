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

interface CategoryPermissionsCheckerInterface
{
    public const VIEW_LEVEL = 'VIEW';

    public const EDIT_LEVEL = 'EDIT';

    public const OWN_LEVEL = 'OWN';

    public const ALL_LEVELS = [
        self::VIEW_LEVEL,
        self::EDIT_LEVEL,
        self::OWN_LEVEL,
    ];

    public function hasAccessToProduct(string $type, ?CategoryAwareInterface $entity, ?UserInterface $user = null): bool;
}