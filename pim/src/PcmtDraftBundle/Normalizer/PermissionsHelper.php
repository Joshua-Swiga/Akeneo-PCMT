<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Tool\Component\Classification\CategoryAwareInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class PermissionsHelper
{
    /** @var CategoryPermissionsCheckerInterface */
    private $categoryPermissionsChecker;

    public function __construct(CategoryPermissionsCheckerInterface $categoryPermissionsChecker)
    {
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function normalizeCategoryPermissions(?CategoryAwareInterface $entity): array
    {
        if (!$entity) {
            return [
                'view' => true,
                'edit' => true,
                'own'  => true,
            ];
        }

        return [
            'view' => $this->categoryPermissionsChecker->hasAccessToProduct(
                CategoryPermissionsCheckerInterface::VIEW_LEVEL,
                $entity
            ),
            'edit' => $this->categoryPermissionsChecker->hasAccessToProduct(
                CategoryPermissionsCheckerInterface::EDIT_LEVEL,
                $entity
            ),
            'own' => $this->categoryPermissionsChecker->hasAccessToProduct(
                CategoryPermissionsCheckerInterface::OWN_LEVEL,
                $entity
            ),
        ];
    }
}