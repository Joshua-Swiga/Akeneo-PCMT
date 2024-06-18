<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Service;

use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Akeneo\UserManagement\Component\Model\User;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccess;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class CategoryPermissionsDefaultProvider
{
    /** @var GroupRepository */
    private $userGroupRepository;

    public function __construct(GroupRepository $userGroupRepository)
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    public function fill(CategoryWithAccess $category): void
    {
        foreach (CategoryPermissionsCheckerInterface::ALL_LEVELS as $level) {
            if (0 === count($category->getAccessesOfLevel($level))) {
                $allGroup = $this->userGroupRepository->getDefaultUserGroup();
                $categoryAccess = new CategoryAccess($category, $allGroup, $level);
                $category->addAccess($categoryAccess);
            }
        }
    }

    public function remove(CategoryWithAccess $category): void
    {
        foreach ($category->getAccesses() as $access) {
            /** @var CategoryAccess $access */
            if (User::GROUP_DEFAULT === $access->getUserGroup()->getName()) {
                $category->removeAccess($access);
            }
        }
    }
}