<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Service;

use Akeneo\Tool\Component\Classification\Repository\CategoryRepositoryInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtPermissionsBundle\Entity\CategoryWithAccessInterface;
use PcmtSharedBundle\Service\CategoryWithPermissionsRepositoryInterface;

class CategoryWithPermissionsRepository implements CategoryWithPermissionsRepositoryInterface
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var CategoryPermissionsChecker */
    private $categoryPermissionsChecker;

    public function __construct(
        CategoryRepositoryInterface $categoryRepository,
        CategoryPermissionsChecker $categoryPermissionsChecker
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryPermissionsChecker = $categoryPermissionsChecker;
    }

    public function getCategoryCodes(string $permissionLevel, ?UserInterface $user = null): ?array
    {
        $categories = $this->categoryRepository->findAll();
        $codes = [];
        foreach ($categories as $category) {
            /** @var CategoryWithAccessInterface $category */
            if ($this->categoryPermissionsChecker->isGranted($permissionLevel, $category, $user)) {
                $codes[] = $category->getCode();
            }
        }

        return $codes;
    }

    public function getCategoryIds(string $permissionLevel): ?array
    {
        $categories = $this->categoryRepository->findAll();
        $ids = [];
        foreach ($categories as $category) {
            /** @var CategoryWithAccessInterface $category */
            if ($this->categoryPermissionsChecker->isGranted($permissionLevel, $category)) {
                $ids[] = $category->getId();
            }
        }

        return $ids;
    }
}
