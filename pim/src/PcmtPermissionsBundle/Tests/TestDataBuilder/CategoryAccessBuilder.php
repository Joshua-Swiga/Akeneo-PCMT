<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\Group;
use PcmtPermissionsBundle\Entity\CategoryAccess;
use PcmtPermissionsBundle\Entity\CategoryWithAccessInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;

class CategoryAccessBuilder
{
    /** @var CategoryWithAccessInterface */
    private $category;

    /** @var Group */
    private $userGroup;

    /** @var string */
    private $accessLevel;

    public function __construct(CategoryWithAccessInterface $category, string $accessLevel = CategoryPermissionsCheckerInterface::VIEW_LEVEL)
    {
        $this->userGroup = (new UserGroupBuilder())->build();
        $this->accessLevel = $accessLevel;
        $this->category = $category;
    }

    public function withUserGroup(Group $group): self
    {
        $this->userGroup = $group;

        return $this;
    }

    public function build(): CategoryAccess
    {
        return new CategoryAccess($this->category, $this->userGroup, $this->accessLevel);
    }
}