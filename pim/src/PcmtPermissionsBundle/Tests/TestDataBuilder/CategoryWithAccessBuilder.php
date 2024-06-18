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
use PcmtPermissionsBundle\Entity\CategoryWithAccess;

class CategoryWithAccessBuilder
{
    /** @var CategoryWithAccess */
    private $categoryWithAccess;

    public function __construct()
    {
        $this->categoryWithAccess = new CategoryWithAccess();
        $this->withDefaultAccesses();
    }

    public function withDefaultAccesses(): self
    {
        $access = (new CategoryAccessBuilder($this->categoryWithAccess))->build();

        return $this->withAccesses([$access]);
    }

    public function withAccesses(iterable $accesses): self
    {
        $this->categoryWithAccess->clearAccesses();
        foreach ($accesses as $access) {
            $this->categoryWithAccess->addAccess($access);
        }

        return $this;
    }

    public function clearAccesses(): self
    {
        $this->categoryWithAccess->clearAccesses();

        return $this;
    }

    public function withAccess(CategoryAccess $access): self
    {
        $this->categoryWithAccess->addAccess($access);

        return $this;
    }

    public function withTwoChildren(): self
    {
        $children = [];
        for ($i = 0; $i < 2; $i++) {
            $children[] = (new self())->build();
        }

        return $this->withChildren($children);
    }

    public function withChildren(iterable $children): self
    {
        foreach ($this->categoryWithAccess->getChildren() as $child) {
            $this->categoryWithAccess->removeChild($child);
        }
        foreach ($children as $child) {
            $this->categoryWithAccess->addChild($child);
        }

        return $this;
    }

    public function withAccessesForGroup(array $accessLevels, Group $group): self
    {
        foreach ($accessLevels as $accessLevel) {
            $access = (new CategoryAccessBuilder($this->categoryWithAccess, $accessLevel))
                ->withUserGroup($group)
                ->build();
            $this->withAccess($access);
        }

        return $this;
    }

    public function withAccessesForGroups(array $accessesLevels, array $groups): self
    {
        foreach ($groups as $group) {
            $this->withAccessesForGroup($accessesLevels, $group);
        }

        return $this;
    }

    public function build(): CategoryWithAccess
    {
        return $this->categoryWithAccess;
    }
}