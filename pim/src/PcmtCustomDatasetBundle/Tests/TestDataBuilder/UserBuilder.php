<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Tests\TestDataBuilder;

use Akeneo\UserManagement\Component\Model\User;

class UserBuilder
{
    public const EXAMPLE_USERNAME = 'admin';

    /** @var User */
    private $user;

    public function __construct()
    {
        $this->user = new User();
        $this->user->setUsername(self::EXAMPLE_USERNAME);
    }

    public function build(): User
    {
        return $this->user;
    }

    public function withUsername(?string $username): self
    {
        $this->user->setUsername($username);

        return $this;
    }
}
