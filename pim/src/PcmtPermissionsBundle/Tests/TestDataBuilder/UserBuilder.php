<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\GroupInterface;
use Akeneo\UserManagement\Component\Model\User;

class UserBuilder
{
    public const EXAMPLE_FIRST_NAME = 'first name';

    public const EXAMPLE_LAST_NAME = 'last name';

    public const DEFAULT_ID = 56;

    /** @var User */
    private $user;

    public function __construct()
    {
        $this->user = new User();
        $this->user->setFirstName(self::EXAMPLE_FIRST_NAME);
        $this->user->setLastName(self::EXAMPLE_LAST_NAME);
        $this->withGroup((new UserGroupBuilder())->build());
        $this->withId(self::DEFAULT_ID);
    }

    public function withId(int $id): self
    {
        $this->user->setId($id);

        return $this;
    }

    public function withGroups(array $userGroups): self
    {
        $this->user->setGroups($userGroups);

        return $this;
    }

    public function withGroup(GroupInterface $group): self
    {
        $this->user->addGroup($group);

        return $this;
    }

    public function build(): User
    {
        return $this->user;
    }
}