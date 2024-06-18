<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Entity;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;

class CategoryAccess
{
    /** @var int */
    private $id;

    /** @var CategoryWithAccessInterface */
    private $category;

    /** @var Group */
    private $userGroup;

    /** @var string */
    private $level = '';

    /**
     * CategoryAccess constructor.
     */
    public function __construct(CategoryWithAccessInterface $category, GroupInterface $userGroup, string $level)
    {
        $this->category = $category;
        $this->userGroup = $userGroup;
        $this->level = $level;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategory(): CategoryWithAccessInterface
    {
        return $this->category;
    }

    public function getUserGroup(): GroupInterface
    {
        return $this->userGroup;
    }

    public function getLevel(): string
    {
        return $this->level;
    }
}