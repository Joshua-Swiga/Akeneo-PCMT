<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\User;

class UserGroupBuilder
{
    /** @var Group */
    private $userGroup;

    public const DEFAULT_ID = 33;

    public const ANOTHER_ID = 35;

    public function __construct()
    {
        $this->userGroup = new Group();
        $this->withId(self::DEFAULT_ID);
        $this->withName(User::GROUP_DEFAULT);
    }

    public function withName(string $name): self
    {
        $this->userGroup->setName($name);

        return $this;
    }

    public function withId(int $id): self
    {
        $reflection = new \ReflectionClass(get_class($this->userGroup));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->userGroup, $id);

        return $this;
    }

    public function buildWithAnotherId(): Group
    {
        $this->withId(self::ANOTHER_ID);

        return $this->userGroup;
    }

    public function build(): Group
    {
        return $this->userGroup;
    }
}